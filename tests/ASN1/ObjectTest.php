<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FG\Test\ASN1;

use FG\ASN1\ExplicitlyTaggedObject;
use FG\Test\ASN1TestCase;
use FG\ASN1\Object;
use FG\ASN1\Identifier;
use FG\ASN1\Universal\BitString;
use FG\ASN1\Universal\Boolean;
use FG\ASN1\Universal\Enumerated;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\Null;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\OctetString;
use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\IA5String;
use FG\ASN1\Universal\PrintableString;

class ObjectTest extends ASN1TestCase
{

    public function testCalculateNumberOfLengthOctets()
    {
        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 32);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 0);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 127);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 128);
        $this->assertEquals(2, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 255);
        $this->assertEquals(2, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\Object');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 1025);
        $this->assertEquals(3, $calculatedNrOfLengthOctets);
    }

    /**
     * For the real parsing tests look in the test cases of each single ASn object.
     */
    public function testFromBinary()
    {
        /** @var BitString $parsedObject */
        $binaryData = chr(Identifier::BITSTRING);
        $binaryData .= chr(0x03);
        $binaryData .= chr(0x05);
        $binaryData .= chr(0xFF);
        $binaryData .= chr(0xA0);

        $expectedObject = new BitString(0xFFA0, 5);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof BitString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());
        $this->assertEquals($expectedObject->getNumberOfUnusedBits(), $parsedObject->getNumberOfUnusedBits());

        /** @var OctetString $parsedObject */
        $binaryData = chr(Identifier::OCTETSTRING);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0xFF);
        $binaryData .= chr(0xA0);

        $expectedObject = new OctetString(0xFFA0);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof OctetString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var \FG\ASN1\Universal\Boolean $parsedObject */
        $binaryData = chr(Identifier::BOOLEAN);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0xFF);

        $expectedObject = new Boolean(true);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Boolean);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var Enumerated $parsedObject */
        $binaryData = chr(Identifier::ENUMERATED);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x03);

        $expectedObject = new Enumerated(3);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Enumerated);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var IA5String $parsedObject */
        $string = 'Hello Foo World!!!11EinsEins!1';
        $binaryData = chr(Identifier::IA5_STRING);
        $binaryData .= chr(strlen($string));
        $binaryData .= $string;

        $expectedObject = new IA5String($string);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof IA5String);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var \FG\ASN1\Universal\Integer $parsedObject */
        $binaryData = chr(Identifier::INTEGER);
        $binaryData .= chr(0x01);
        $binaryData .= chr(123);

        $expectedObject = new Integer(123);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Integer);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var \FG\ASN1\Universal\Null $parsedObject */
        $binaryData = chr(Identifier::NULL);
        $binaryData .= chr(0x00);

        $expectedObject = new Null();
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Null);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var ObjectIdentifier $parsedObject */
        $binaryData = chr(Identifier::OBJECT_IDENTIFIER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(1 * 40 + 2);
        $binaryData .= chr(3);

        $expectedObject = new ObjectIdentifier('1.2.3');
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof ObjectIdentifier);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var PrintableString $parsedObject */
        $string = 'This is a test string. #?!%&""';
        $binaryData = chr(Identifier::PRINTABLE_STRING);
        $binaryData .= chr(strlen($string));
        $binaryData .= $string;

        $expectedObject = new PrintableString($string);
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof PrintableString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /** @var Sequence $parsedObject */
        $binaryData = chr(Identifier::SEQUENCE);
        $binaryData .= chr(0x06);
        $binaryData .= chr(Identifier::BOOLEAN);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x00);
        $binaryData .= chr(Identifier::INTEGER);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x03);

        $expectedChild1 = new Boolean(false);
        $expectedChild2 = new Integer(0x03);

        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Sequence);
        $this->assertEquals(2, $parsedObject->getNumberOfChildren());

        $children = $parsedObject->getChildren();
        $child1 = $children[0];
        $child2 = $children[1];
        $this->assertEquals($expectedChild1->getContent(), $child1->getContent());
        $this->assertEquals($expectedChild2->getContent(), $child2->getContent());

        /** @var ExplicitlyTaggedObject $parsedObject */
        $taggedObject = new ExplicitlyTaggedObject(0x01, new PrintableString('Hello tagged world'));
        $binaryData = $taggedObject->getBinary();
        $parsedObject = Object::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof ExplicitlyTaggedObject);
    }

    /**
     * @expectedException \FG\ASN1\Exception\ParserException
     * @expectedExceptionMessage ASN.1 Parser Exception at offset 10: Can not parse binary from data: Offset index larger than input size
     * @depends testFromBinary
     */
    public function testFromBinaryThrowsException()
    {
        $binaryData = 0x0;
        $offset = 10;
        Object::fromBinary($binaryData, $offset);
    }
}
