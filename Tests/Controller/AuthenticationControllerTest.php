<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationControllerTest extends WebTestCase
{
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
        $this->assertGreaterThan(0, $csrfToken->count(), 'Did not find a CSRF token.');
        $this->assertGreaterThan(0, $rememberMe->count(), 'Did not find a remember me field.');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
