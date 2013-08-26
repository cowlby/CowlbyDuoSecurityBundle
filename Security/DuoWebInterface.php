<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Security;

interface DuoWebInterface
{
    public function getHost();

    public function signRequest($username);

    public function verifyResponse($sigResponse);
}
