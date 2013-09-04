<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationControllerTest extends WebTestCase
{
    protected static $class = 'Cowlby\Bundle\DuoSecurityBundle\Tests\Fixtures\App\app\AppKernel';

    public function testLoginAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $username = $crawler->filter("input[type='text'][name='_username']");
        $password = $crawler->filter("input[type='password'][name='_password']");
        $csrfToken = $crawler->filter("input[type='hidden'][name='_csrf_token']");
        $rememberMe = $crawler->filter("input[type='checkbox'][name='_remember_me']");

        $this->assertGreaterThan(0, $username->count(), 'Did not find a username field.');
        $this->assertGreaterThan(0, $password->count(), 'Did not find a password field.');
        $this->assertGreaterThan(0, $csrfToken->count(), 'Did not find a CSRF token field.');
        $this->assertGreaterThan(0, $rememberMe->count(), 'Did not find a remember me field.');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testLoginSubmitWithDuoSecurityEnabled()
    {
        $client = static::createClient(array('environment' => 'security_duo_enabled'));

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'user';
        $form['_password'] = 'userpass';

        $crawler = $client->submit($form);

        $this->assertEquals('/duo_check', $client->getRequest()->getPathInfo(), 'Did not go to the /duo_check path.');

        $iframe = $crawler->filter('iframe#duo_iframe');
        $form = $crawler->filter('form#duo_form');
        $rememberMe = $crawler->filter("input[type='hidden'][name='_remember_me']");

        $this->assertGreaterThan(0, $iframe->count(), 'Did not find the Duo Web iframe.');
        $this->assertGreaterThan(0, $form->count(), 'Did not find the Duo Web form.');
        $this->assertGreaterThan(0, $rememberMe->count(), 'Did not find the remember me field.');

        $path = $client->getContainer()->get('router')->generate('cowlby_duo_security_duo_verify');
        $duoVerify = $crawler->filter("html:contains('$path')");
        $this->assertGreaterThan(0, $duoVerify->count(), 'Did not find /duo_verify in post_action');

        $sigRequestParam = $crawler->filter('html:contains("sig_request")');
        $postActionParam = $crawler->filter('html:contains("post_action")');
        $host = $client->getContainer()->getParameter('cowlby_duo_security.duo.host');
        $hostParam = $crawler->filter("html:contains('$host')");

        $this->assertGreaterThan(0, $sigRequestParam->count(), 'Did not find the sig_request parameter');
        $this->assertGreaterThan(0, $postActionParam->count(), 'Did not find the post_action parameter');
        $this->assertGreaterThan(0, $hostParam->count(), 'Did not find the host parameter');
    }

    public function testLoginSubmitWithDuoSecurityDisabled()
    {
        $client = static::createClient(array('environment' => 'security_duo_disabled'));

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'user';
        $form['_password'] = 'userpass';

        $crawler = $client->submit($form);

        $this->assertEquals('/duo_check', $client->getRequest()->getPathInfo(), 'Did not go to the /duo_check for login check.');

        $crawler = $client->followRedirect();

        $this->assertEquals('/', $client->getRequest()->getPathInfo(), 'Did not redirect to / after login.');
    }

    public function testDuoWebResponseLogin()
    {
        $client = static::createClient(array('environment' => 'security_duo_enabled'));
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $crawler = $client->request('POST', '/duo_verify', array('sig_response' => $sigResponse));
        $crawler = $client->followRedirect();

        $error = $crawler->filter('div.alert-error');

        $this->assertNotEmpty($error->text(), 'No error in form.');
        $this->assertGreaterThan(0, $error->count(), 'Did not redirect to login with error.');
    }

    public function testCsrfTokenWhenEnabled()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $csrfToken = $crawler->filter("input[type='hidden'][name='_csrf_token']");

        $this->assertRegExp('/^[a-f0-9]{40}$/', $csrfToken->attr('value'), 'Did not find valid token.');
    }

    public function testCsrfTokenWhenDisabled()
    {
        $client = static::createClient(array('environment' => 'csrf_disabled'));
        $crawler = $client->request('GET', '/login');

        $csrfToken = $crawler->filter("input[type='hidden'][name='_csrf_token']");

        $this->assertEmpty($csrfToken->attr('value'), 'CSRF token field not empty');
    }

    public function testLastUsernameFeature()
    {
        $client = static::createClient();

        $session = $client->getContainer()->get('session');
        $session->set(SecurityContextInterface::LAST_USERNAME, 'last_username');
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $crawler = $client->request('GET', '/login');
        $username = $crawler->filter("input[type='text'][name='_username'][value='last_username']");

        $this->assertGreaterThan(0, $username->count(), 'Did not find username field with last_username value.');
    }

    public function testAuthenticationErrorFeature()
    {
        $client = static::createClient();

        $session = $client->getContainer()->get('session');
        $session->set(SecurityContextInterface::AUTHENTICATION_ERROR, new AuthenticationException('mock error'));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $crawler = $client->request('GET', '/login');
        $error = $crawler->filter("html:contains('mock error')");

        $this->assertGreaterThan(0, $error->count(), 'Did not find error in html.');
    }
}
