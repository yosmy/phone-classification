<?php

namespace Yosmy\Phone;

/**
 * @di\service()
 */
class ResolveClassification
{
    /**
     * @var Carrier\ExecuteLookup
     */
    private $executeLookup;

    /**
     * @var PickCarrier
     */
    private $pickCarrier;

    /**
     * @var AddUntypedCarrier
     */
    private $addUntypedCarrier;

    /**
     * @param Carrier\ExecuteLookup $executeLookup
     * @param PickCarrier           $pickCarrier
     * @param AddUntypedCarrier     $addUntypedCarrier
     */
    public function __construct(
        Carrier\ExecuteLookup $executeLookup,
        PickCarrier $pickCarrier,
        AddUntypedCarrier $addUntypedCarrier
    ) {
        $this->executeLookup = $executeLookup;
        $this->pickCarrier = $pickCarrier;
        $this->addUntypedCarrier = $addUntypedCarrier;
    }

    /**
     * @param string $country
     * @param string $prefix
     * @param string $number
     *
     * @return Classification
     *
     * @throws UnresolvableClassificationException
     */
    public function resolve(
        string $country,
        string $prefix,
        string $number
    ): Classification {
        try {
            $lookup = $this->executeLookup->execute(
                $country,
                $prefix,
                $number
            );
        } catch (Carrier\UnresolvableLookupException $e) {
            throw new UnresolvableClassificationException();
        }

        if (
            !$lookup->getMnc()
            || !$lookup->getMcc()
            || !$lookup->getName()
        ) {
            throw new UnresolvableClassificationException();
        }

        try {
            $carrier = $this->pickCarrier->pick(
                $country,
                $lookup->getMcc(),
                $lookup->getMnc()
            );
        } catch (NonexistentCarrierException $e) {
            $this->addUntypedCarrier->add(
                $country,
                $lookup->getMcc(),
                $lookup->getMnc(),
                $lookup->getName()
            );

            throw new UnresolvableClassificationException();
        }

        if (!$carrier->getType()) {
            throw new UnresolvableClassificationException();
        }

        $voip = $carrier->getType() == Carrier::TYPE_VOIP;

        return new Classification(
            $voip
        );
    }
}