<?php

namespace Yosmy\Phone\Test;

use PHPUnit\Framework\TestCase;
use Yosmy;
use LogicException;

class ResolveClassificationTest extends TestCase
{
    public function testResolve()
    {
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $executeLookup = $this->createMock(Yosmy\Phone\Carrier\ExecuteLookup::class);

        $lookup = new Yosmy\Phone\Carrier\Lookup(
            'mcc-1',
            'mnc-1',
            'name'
        );

        $executeLookup->expects($this->once())
            ->method('execute')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willReturn($lookup);

        $pickCarrier = $this->createMock(Yosmy\Phone\PickCarrier::class);

        $carrier = $this->createMock(Yosmy\Phone\Carrier::class);

        $carrier->expects($this->exactly(2))
            ->method('getType')
            ->with()
            ->willReturn("mobile");

        $pickCarrier->expects($this->once())
            ->method('pick')
            ->with(
                $country,
                $lookup->getMcc(),
                $lookup->getMnc()
            )
            ->willReturn($carrier);

        $addUntypedCarrier = $this->createMock(Yosmy\Phone\AddUntypedCarrier::class);

        $resolveLookup = new Yosmy\Phone\ResolveClassification(
            $executeLookup,
            $pickCarrier,
            $addUntypedCarrier
        );

        try {
            $expectedLookup = $resolveLookup->resolve(
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\Phone\UnresolvableClassificationException $e) {
            throw new LogicException();
        }

        $lookup = new Yosmy\Phone\Classification(
            false
        );

        $this->assertEquals(
            $expectedLookup,
            $lookup
        );
    }

    /**
     * @throws Yosmy\Phone\UnresolvableClassificationException
     */
    public function testResolveHavingUnresolvableLookupException()
    {
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $executeLookup = $this->createMock(Yosmy\Phone\Carrier\ExecuteLookup::class);

        $executeLookup->expects($this->once())
            ->method('execute')
            ->willThrowException(new Yosmy\Phone\Carrier\UnresolvableLookupException());

        $pickCarrier = $this->createMock(Yosmy\Phone\PickCarrier::class);

        $addUntypedCarrier = $this->createMock(Yosmy\Phone\AddUntypedCarrier::class);

        $resolveLookup = new Yosmy\Phone\ResolveClassification(
            $executeLookup,
            $pickCarrier,
            $addUntypedCarrier
        );

        $this->expectExceptionObject(new Yosmy\Phone\UnresolvableClassificationException());

        $resolveLookup->resolve(
            $country,
            $prefix,
            $number
        );
    }

    /**
     * @throws Yosmy\Phone\UnresolvableClassificationException
     */
    public function testResolveHavingNonexistentCarrierException()
    {
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $executeLookup = $this->createMock(Yosmy\Phone\Carrier\ExecuteLookup::class);

        $lookup = new Yosmy\Phone\Carrier\Lookup(
            'mcc-1',
            'mnc-1',
            'name'
        );

        $executeLookup->expects($this->once())
            ->method('execute')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willReturn($lookup);

        $pickCarrier = $this->createMock(Yosmy\Phone\PickCarrier::class);

        $pickCarrier->expects($this->once())
            ->method('pick')
            ->willThrowException(new Yosmy\Phone\NonexistentCarrierException());

        $addUntypedCarrier = $this->createMock(Yosmy\Phone\AddUntypedCarrier::class);

        $addUntypedCarrier->expects($this->once())
            ->method('add')
            ->with(
                $country,
                $lookup->getMcc(),
                $lookup->getMnc(),
                $lookup->getName()
            );

        $resolveLookup = new Yosmy\Phone\ResolveClassification(
            $executeLookup,
            $pickCarrier,
            $addUntypedCarrier
        );

        $this->expectExceptionObject(new Yosmy\Phone\UnresolvableClassificationException());

        $resolveLookup->resolve(
            $country,
            $prefix,
            $number
        );
    }

    /**
     * @throws Yosmy\Phone\UnresolvableClassificationException
     */
    public function testResolveHavingEmptyType()
    {
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $executeLookup = $this->createMock(Yosmy\Phone\Carrier\ExecuteLookup::class);

        $lookup = new Yosmy\Phone\Carrier\Lookup(
            'mcc-1',
            'mnc-1',
            'name'
        );

        $executeLookup->expects($this->once())
            ->method('execute')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willReturn($lookup);

        $pickCarrier = $this->createMock(Yosmy\Phone\PickCarrier::class);

        $carrier = $this->createMock(Yosmy\Phone\Carrier::class);

        $carrier->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $pickCarrier->expects($this->once())
            ->method('pick')
            ->with(
                $country,
                $lookup->getMcc(),
                $lookup->getMnc()
            )
            ->willReturn($carrier);

        $addUntypedCarrier = $this->createMock(Yosmy\Phone\AddUntypedCarrier::class);

        $resolveLookup = new Yosmy\Phone\ResolveClassification(
            $executeLookup,
            $pickCarrier,
            $addUntypedCarrier
        );

        $this->expectExceptionObject(new Yosmy\Phone\UnresolvableClassificationException());

        $resolveLookup->resolve(
            $country,
            $prefix,
            $number
        );
    }

    /**
     * @dataProvider carrierLookupProvider
     *
     * @param string $mcc
     * @param string $mnc
     * @param string $name
     *
     * @throws Yosmy\Phone\UnresolvableClassificationException
     */
    public function testResolveWithEmptyValuesOnCarrierLookup(
        string $mcc,
        string $mnc,
        string $name
    ) {
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $executeLookup = $this->createMock(Yosmy\Phone\Carrier\ExecuteLookup::class);

        $lookup = new Yosmy\Phone\Carrier\Lookup(
            $mcc,
            $mnc,
            $name
        );

        $executeLookup->expects($this->once())
            ->method('execute')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willReturn($lookup);

        $pickCarrier = $this->createMock(Yosmy\Phone\PickCarrier::class);

        $addUntypedCarrier = $this->createMock(Yosmy\Phone\AddUntypedCarrier::class);

        $resolveLookup = new Yosmy\Phone\ResolveClassification(
            $executeLookup,
            $pickCarrier,
            $addUntypedCarrier
        );

        $this->expectExceptionObject(new Yosmy\Phone\UnresolvableClassificationException());

        try {
            $resolveLookup->resolve(
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\Phone\UnresolvableClassificationException $e) {
            throw $e;
        }
    }

    public function carrierLookupProvider()
    {
        return [
            'empty_name'  => [
                'name' => '',
                'mcc' => 'Mcc 1',
                'mnc' => 'Mnc 1'
            ],
            'empty_mobile_country_code'  => [
                'name' => '',
                'mcc' => 'Mcc 1',
                'mnc' => 'Mnc 1',
            ],
            'empty_mobile_network_code'  => [
                'name' => '',
                'mcc' => 'Mcc 1',
                'mnc' => 'Mnc 1',
            ],
        ];
    }
}