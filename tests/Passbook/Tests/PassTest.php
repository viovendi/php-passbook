<?php

namespace Passbook\Tests;

use Passbook\Pass;
use Passbook\PassFactory;
use Passbook\Pass\Field;
use Passbook\Pass\Barcode;
use Passbook\Pass\Beacon;
use Passbook\Pass\Location;
use Passbook\Pass\Structure;
use Passbook\Type\BoardingPass;
use Passbook\Type\Coupon;
use Passbook\Type\EventTicket;
use Passbook\Type\Generic;
use Passbook\Type\StoreCard;

class PassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var EventTicket
     */
    protected $eventTicket;

    /**
     * @var Generic
     */
    protected $generic;

    /**
     * @var StoreCard
     */
    protected $storeCard;

    /**
     * @var Pass
     */
    protected $pass;

    /**
     * Boarding Pass
     */
    public function testBoardingPass()
    {
        $boardingPass = new BoardingPass(uniqid(), 'SFO to JFK', BoardingPass::TYPE_AIR);

        // Set colors
        $boardingPass->setBackgroundColor('rgb(22, 55, 110)');
        $boardingPass->setForegroundColor('rgb(50, 91, 185)');

        // Logo text
        $boardingPass->setLogoText('Skyport Airways');

        // Relevant date
        $boardingPass->setRelevantDate(new \DateTime());

        // Add location
        $location = new Location(-122.3748889, 37.6189722);
        $boardingPass->addLocation($location);

        // Create pass structure
        $structure = new Structure();

        // Add header field
        $header = new Field('gate', '23');
        $header->setLabel('GATE');
        $structure->addHeaderField($header);

        // Add primary fields
        $primary = new Field('depart', 'SFO');
        $primary->setLabel('SAN FRANCISCO');
        $structure->addPrimaryField($primary);

        $primary = new Field('arrive', 'JFK');
        $primary->setLabel('NEW YORK');
        $structure->addPrimaryField($primary);

        // Add secondary field
        $secondary = new Field('passenger', 'John Appleseed');
        $secondary->setLabel('PASSENGER');
        $structure->addSecondaryField($secondary);

        // Add auxiliary fields
        $auxiliary = new Field('boardingTime', '2:25 PM');
        $auxiliary->setLabel('DEPART');
        $structure->addAuxiliaryField($auxiliary);

        $auxiliary = new Field('flightNewName', '815');
        $auxiliary->setLabel('FLIGHT');
        $structure->addAuxiliaryField($auxiliary);

        $auxiliary = new Field('class', 'Coach');
        $auxiliary->setLabel('DESIG.');
        $structure->addAuxiliaryField($auxiliary);

        $auxiliary = new Field('date', '7/22');
        $auxiliary->setLabel('DATE');
        $structure->addAuxiliaryField($auxiliary);

        // Set pass structure
        $boardingPass->setStructure($structure);

        // Add barcode
        $barcode = new Barcode(Barcode::TYPE_PDF_417, 'SFOJFK JOHN APPLESEED LH451 2012-07-22T14:25-08:00');
        $boardingPass->setBarcode($barcode);

        $json = PassFactory::serialize($boardingPass);
        $array = json_decode($json, true);

        $this->assertArrayHasKey('transitType', $array[$boardingPass->getType()]);
    }

    /**
     * Store Card
     */
    public function testStoreCard()
    {
        $json = PassFactory::serialize($this->storeCard);
        $array = json_decode($json, true);
    }

    /**
     * Event Ticket
     */
    public function testEventTicket()
    {
        $this->eventTicket->setBackgroundColor('rgb(60, 65, 76)');
        $this->assertSame('rgb(60, 65, 76)', $this->eventTicket->getBackgroundColor());
        $this->eventTicket->setLogoText('Apple Inc.');
        $this->assertSame('Apple Inc.', $this->eventTicket->getLogoText());

        // Add location
        $location = new Location(59.33792, 18.06873);
        $this->eventTicket->addLocation($location);

        // Create pass structure
        $structure = new Structure();

        // Add primary field
        $primary = new Field('event', 'The Beat Goes On');
        $primary->setLabel('Event');
        $structure->addPrimaryField($primary);

        // Add secondary field
        $secondary = new Field('location', 'Moscone West');
        $secondary->setLabel('Location');
        $structure->addSecondaryField($secondary);

        // Add auxiliary field
        $auxiliary = new Field('datetime', '2013-04-15 @10:25');
        $auxiliary->setLabel('Date & Time');
        $structure->addAuxiliaryField($auxiliary);

        // Relevant date
        $this->eventTicket->setRelevantDate(new \DateTime());

        // Set pass structure
        $this->eventTicket->setStructure($structure);

        // Add barcode
        $barcode = new Barcode('PKBarcodeFormatQR', 'barcodeMessage');
        $this->eventTicket->setBarcode($barcode);

        $json = PassFactory::serialize($this->eventTicket);
        $array = json_decode($json, true);

        $this->assertArrayHasKey('eventTicket', $array);
        $this->assertArrayHasKey('locations', $array);
        $this->assertArrayHasKey('barcode', $array);
        $this->assertArrayHasKey('logoText', $array);
        $this->assertArrayHasKey('backgroundColor', $array);
        $this->assertArrayHasKey('eventTicket', $array);
        $this->assertArrayHasKey('relevantDate', $array);
    }

    /**
     * Generic
     */
    public function testGeneric()
    {
        $this->generic->setBackgroundColor('rgb(60, 65, 76)');
        $this->assertSame('rgb(60, 65, 76)', $this->generic->getBackgroundColor());
        $this->generic->setLogoText('Apple Inc.');
        $this->assertSame('Apple Inc.', $this->generic->getLogoText());

        $this->generic
            ->setFormatVersion(1)
            ->setDescription('description')
        ;

        // Create pass structure
        $structure = new Structure();

        // Add primary field
        $primary = new Field('event', 'The Beat Goes On');
        $primary->setLabel('Event');
        $structure->addPrimaryField($primary);

        // Add back field
        $back = new Field('back', 'Hello World!');
        $back->setLabel('Location');
        $structure->addSecondaryField($back);

        // Add auxiliary field
        $auxiliary = new Field('datetime', '2014 Aug 1');
        $auxiliary->setLabel('Date & Time');
        $structure->addAuxiliaryField($auxiliary);

        // Set pass structure
        $this->generic->setStructure($structure);

        // Add beacon
        $beacon = new Beacon('abcdef01-2345-6789-abcd-ef0123456789');
        $this->generic->addBeacon($beacon);

        $json = PassFactory::serialize($this->generic);
        $array = json_decode($json, true);

        $this->assertArrayHasKey('beacons', $array);
        $this->assertArrayHasKey('generic', $array);
    }

    /**
     * Pass
     */
    public function testPass()
    {
        $this->pass
            ->setWebServiceURL('http://example.com')
            ->setForegroundColor('rgb(0, 255, 0)')
            ->setBackgroundColor('rgb(0, 255, 0)')
            ->setLabelColor('rgb(0, 255, 0)')
            ->setAuthenticationToken('123')
            ->setType('generic')
            ->setSuppressStripShine(false)
            ->setAppLaunchURL('http://app.launch.url')
            ->addAssociatedStoreIdentifier(123)
        ;

        $properties = array(
            'webServiceURL',
            'foregroundColor',
            'backgroundColor',
            'labelColor',
            'authenticationToken',
            'suppressStripShine',
            'associatedStoreIdentifiers',
            'appLaunchURL',
        );
        $array = $this->pass->toArray();
        foreach ($properties as $property) {
            $this->assertTrue(isset($array[$property]));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->coupon       = new Coupon(uniqid(), 'Lorem ipsum');
        $this->eventTicket  = new EventTicket(uniqid(), 'Lorem ipsum');
        $this->generic      = new Generic(uniqid(), 'Lorem ipsum');
        $this->storeCard    = new StoreCard(uniqid(), 'Lorem ipsum');
        $this->pass         = new Pass(uniqid(), 'Lorem ipsum');
    }
}
