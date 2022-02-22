<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\DependencyInjection;

use Marello\Bundle\PaymentTermBundle\DependencyInjection\MarelloPaymentTermExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class MarelloPaymentTermExtensionTest extends ExtensionTestCase
{
    /**
     * @var MarelloPaymentTermExtension
     */
    protected $extension;

    protected function setUp(): void
    {
        $this->extension = new MarelloPaymentTermExtension();
    }

    protected function tearDown(): void
    {
        unset($this->extension);
    }

    public function testLoad()
    {
        $this->loadExtension($this->extension);

        $this->assertDefinitionsLoaded([
            'marello_payment_term.provider.payment_term',
            'marello_payment_term.provider.payment_term_delete_permission',
            'marello_payment_term.action_permissions.payment_term',
            'marello_payment_term.payment_term.form.type',
            'marello_payment_term.payment_term.form',
            'marello_payment_term.payment_term.form.handler',
            'marello_payment.form_type.payment_term_choice',
        ]);
    }
}
