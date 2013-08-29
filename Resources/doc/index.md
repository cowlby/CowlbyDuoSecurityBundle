Getting started with CowlbyDuoSecurityBundle
============================================

The CowlbyDuoSecurityBundle is built on top of the Symfony2 security framework
to provide two-factor authentication via [Duo Security][1]. It uses the
[Duo Web][2] integration type to perform the two-factor authentication.


Installation
------------

### Step 1: Download via Composer

You can install this bundle using composer:

    php composer.phar require cowlby/duo-security-bundle

or add the package to your composer.json file directly:

    {
        "require": {
            "cowlby/duo-security-bundle": "dev-master"
        }
    }

Run composer to download the bundle:

    php composer.phar update cowlby/duo-security-bundle


### Step 2: Enable the bundle

Add the bundle to your application's kernel:

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Cowlby\Bundle\DuoSecurityBundle(),
        // ...
    );

### Step 3: Import the routes

Add the bundle's routes to your application:

    # app/config/routing.yml
    cowlby_duo_security:
        resource: "@CowlbyDuoSecurityBundle/Resources/config/routing.xml"
        prefix:   /


Configuration
-------------

### Step 1: Create a Duo Security integration

To start, create or us an existing Duo Security integration to use with your
application. Follow the instructions at [Getting Started with Duo Security][3]
and create a `Duo Web` integration. You will need the Integration key (ikey),
Secret key (skey), and the API hostname (host) to configure Symfony. You will
also need to generate a random 40-character Application key (akey).

### Step 2: Configure CowlbyDuoSecurityBundle

Configure the  bundle in your `config.xml` file by providing your integration
details. Use parameters to keep the sensitive data in the `parameters.yml`
file.

    # app/config/config.yml
    cowlby_duo_security:
        duo:
            ikey: %duo_ikey%
            skey: %duo_skey%
            akey: %duo_akey%
            host: %duo_host%

    # app/config/parameters.yml
    parameters:
        duo_ikey: your_integration_ikey
        duo_skey: your_integration_skey
        duo_akey: your_integration_akey
        duo_host: api-XXXXXXXX.duosecurity.com

### Step 3: Configure security.yml

In order to secure your application with CowlbyDuoSecurityBundle, you must
configure the security component in your `security.yml` file.

Below is an example of the configuration necessary to use the
CowlbyDuoSecurityBundle in your application.

    # app/config/security.yml
    security:
        encoders:
            Symfony\Component\Security\Core\User\User: plaintext

        role_hierarchy:
            ROLE_ADMIN: ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

        providers:
            in_memory:
                memory:
                    users:
                        user: { password: userpass, roles: [ 'ROLE_USER' ] }
                        admin: { password: adminpass, roles: [ 'ROLE_ADMIN' ] }

        firewalls:
            main:
                pattern: ^/
                cowlby_duo_security_login: ~
                cowlby_duo_security_form_login: ~
                logout:
                    path:   cowlby_duo_security_logout
                    target: cowlby_duo_security_login
                anonymous: true

        access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }

In this config we've created a firewall named `main` and have told Symfony to
secure it with the `cowlby_duo_security_login` authentication listener and the
`cowlby_duo_security_form_login` authentication listener.

As in the example above, we need to define two authentication listeners to make
two-factor authentication work. Secondary authentication is performed by the
`cowlby_duo_security_login` listener. This listener intercepts requests to the
firewall with a signed Duo Web response parameter and authenticates as
necessary.

Primary authentication must be handled by a separate listener with Duo Web
capabilities. The `cowlby_duo_security_form_login` listener used above is a
sample form based authentication listener. It performs primary authentication
and starts the Duo Web authentication process when necessary. Custom
authentication listeners can be modeled off of this listener.

For more information on configuring the security.yml file please read the
Symfony2 security component [documentation][4].


Usage
-----

A usage guide will be provided shortly.


Next Steps
----------

Once you have completed the basic installation and configuration, you can try
creating your own custom authentication listener and integrate Duo Security
into it. See the `UsernamePasswordFormAuthenticationListener` class provided
in the bundle for an example on how to accomplish this.

A Configuration Reference will be provided shortly.


[1]: http://duosecurity.com/ "Duo Security"
[2]: https://www.duosecurity.com/docs/duoweb "Duo Web Documentation"
[3]: https://www.duosecurity.com/docs/getting_started "Getting Started with Duo Security"
[4]: http://symfony.com/doc/current/book/security.html "Symfony Security"
