<?php

namespace JordJD\LaravelPasswordSecurityAudit\Tests;

use JordJD\LaravelPasswordSecurityAudit\Console\Commands\PasswordAudit;
use JordJD\LaravelPasswordSecurityAudit\Objects\CrackedUser;
use PHPUnit\Framework\TestCase;

class PasswordAuditTest extends TestCase
{
    public function testCrackedUserAutoloadsAndProvidesSafeOutput()
    {
        $user = new CrackedUser(42, 'secret', 'hash');

        $this->assertSame(['key' => 42], $user->toSafeArray());
        $this->assertSame(['key' => 42, 'password' => 'secret', 'hash' => 'hash'], $user->toArray());
    }

    public function testCommandDeclaresSecretOutputAsOptIn()
    {
        $command = new PasswordAudit();

        $this->assertTrue($command->getDefinition()->hasOption('show-secrets'));
    }
}
