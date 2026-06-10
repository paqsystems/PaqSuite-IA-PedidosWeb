<?php

namespace Tests\Unit;

use App\Support\PasswordRecoveryMailLocaleResolver;
use Tests\TestCase;

final class PasswordRecoveryMailLocaleResolverTest extends TestCase
{
    private PasswordRecoveryMailLocaleResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new PasswordRecoveryMailLocaleResolver();
    }

    public function testBodyLocaleHasPriorityOverAcceptLanguage(): void
    {
        $this->assertSame('it', $this->resolver->resolve('it', 'en-US,en;q=0.9'));
    }

    public function testAcceptLanguageIsUsedWhenBodyLocaleIsMissing(): void
    {
        $this->assertSame('en', $this->resolver->resolve(null, 'en-US,en;q=0.9'));
    }

    public function testInvalidInputsFallbackToSpanish(): void
    {
        $this->assertSame('es', $this->resolver->resolve('xx', 'zz-ZZ'));
    }
}
