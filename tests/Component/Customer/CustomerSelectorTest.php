<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Component\Tests\Customer;

use PHPUnit\Framework\TestCase;
use Sonata\Component\Basket\Basket;
use Sonata\Component\Customer\CustomerSelector;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class User
{
    public function getId()
    {
        return 1;
    }
}

class CustomerSelectorTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testUserNotConnected()
    {
        $customer = $this->createMock('Sonata\Component\Customer\CustomerInterface');
        $customerManager = $this->createMock('Sonata\Component\Customer\CustomerManagerInterface');
        $customerManager->expects($this->once())->method('create')->will($this->returnValue($customer));

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');

        $securityContext = $this->createMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->returnValue(false));

        $localeDetector = $this->createMock('Sonata\IntlBundle\Locale\LocaleDetectorInterface');
        $localeDetector->expects($this->once())->method('getLocale')->will($this->returnValue('en'));

        $customerSelector = new CustomerSelector($customerManager, $session, $securityContext, $localeDetector);

        $customer = $customerSelector->get();

        $this->assertInstanceOf('Sonata\Component\Customer\CustomerInterface', $customer);
    }

    public function testInvalidUserType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be an instance of Symfony\\Component\\Security\\Core\\User\\UserInterface');

        $customerManager = $this->createMock('Sonata\Component\Customer\CustomerManagerInterface');

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->will($this->returnValue(new User()));

        $securityContext = $this->createMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $securityContext->expects($this->once())->method('getToken')->will($this->returnValue($token));

        $localeDetector = $this->createMock('Sonata\IntlBundle\Locale\LocaleDetectorInterface');
        $localeDetector->expects($this->once())->method('getLocale')->will($this->returnValue('en'));

        $customerSelector = new CustomerSelector($customerManager, $session, $securityContext, $localeDetector);

        $customerSelector->get();
    }

    public function testExistingCustomer()
    {
        $customer = $this->createMock('Sonata\Component\Customer\CustomerInterface');

        $customerManager = $this->createMock('Sonata\Component\Customer\CustomerManagerInterface');
        $customerManager->expects($this->once())->method('findOneBy')->will($this->returnValue($customer));

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');

        $user = new ValidUser();

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->will($this->returnValue($user));

        $securityContext = $this->createMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $securityContext->expects($this->once())->method('getToken')->will($this->returnValue($token));

        $localeDetector = $this->createMock('Sonata\IntlBundle\Locale\LocaleDetectorInterface');
        $localeDetector->expects($this->once())->method('getLocale')->will($this->returnValue('en'));

        $customerSelector = new CustomerSelector($customerManager, $session, $securityContext, $localeDetector);

        $customer = $customerSelector->get();

        $this->assertInstanceOf('Sonata\Component\Customer\CustomerInterface', $customer);
    }

    public function testNonExistingCustomerNonInSession()
    {
        $customer = $this->createMock('Sonata\Component\Customer\CustomerInterface');

        $customerManager = $this->createMock('Sonata\Component\Customer\CustomerManagerInterface');
        $customerManager->expects($this->once())->method('findOneBy')->will($this->returnValue(false));
        $customerManager->expects($this->once())->method('create')->will($this->returnValue($customer));

        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');

        $user = new ValidUser();

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->will($this->returnValue($user));

        $securityContext = $this->createMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $securityContext->expects($this->once())->method('getToken')->will($this->returnValue($token));

        $localeDetector = $this->createMock('Sonata\IntlBundle\Locale\LocaleDetectorInterface');
        $localeDetector->expects($this->once())->method('getLocale')->will($this->returnValue('en'));

        $customerSelector = new CustomerSelector($customerManager, $session, $securityContext, $localeDetector);

        $customer = $customerSelector->get();

        $this->assertInstanceOf('Sonata\Component\Customer\CustomerInterface', $customer);
    }

    public function testNonExistingCustomerInSession()
    {
        $customer = $this->createMock('Sonata\Component\Customer\CustomerInterface');

        $customerManager = $this->createMock('Sonata\Component\Customer\CustomerManagerInterface');
        $customerManager->expects($this->once())->method('findOneBy')->will($this->returnValue(false));

        $basket = $this->createMock('Sonata\Component\Basket\BasketInterface');
        $basket->expects($this->exactly(2))->method('getCustomer')->will($this->returnValue($customer));

        $session = new Session(new MockArraySessionStorage());
        $session->set('sonata/basket/factory/customer/new', $basket);

        $user = new ValidUser();

        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getUser')->will($this->returnValue($user));

        $securityContext = $this->createMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->returnValue(true));
        $securityContext->expects($this->once())->method('getToken')->will($this->returnValue($token));

        $localeDetector = $this->createMock('Sonata\IntlBundle\Locale\LocaleDetectorInterface');
        $localeDetector->expects($this->once())->method('getLocale')->will($this->returnValue('en'));

        $customerSelector = new CustomerSelector($customerManager, $session, $securityContext, $localeDetector);

        $customer = $customerSelector->get();

        $this->assertInstanceOf('Sonata\Component\Customer\CustomerInterface', $customer);
    }
}
