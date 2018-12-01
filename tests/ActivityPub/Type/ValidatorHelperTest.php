<?php

namespace ActivityPubTest;


use ActivityPub\Type\Core\Activity;
use ActivityPub\Type\Core\Collection;
use ActivityPub\Type\Core\CollectionPage;
use ActivityPub\Type\Core\IntransitiveActivity;
use ActivityPub\Type\Core\Link;
use ActivityPub\Type\Core\ObjectType;
use ActivityPub\Type\Core\OrderedCollection;
use ActivityPub\Type\Core\OrderedCollectionPage;
use ActivityPub\Type\Extended\Actor\Application;
use ActivityPub\Type\Extended\Actor\Group;
use ActivityPub\Type\Extended\Actor\Organization;
use ActivityPub\Type\Extended\Actor\Person;
use ActivityPub\Type\Extended\Actor\Service;
use ActivityPub\Type\Extended\Activity\Accept;
use ActivityPub\Type\Extended\Activity\Add;
use ActivityPub\Type\Extended\Activity\Announce;
use ActivityPub\Type\Extended\Activity\Arrive;
use ActivityPub\Type\Extended\Activity\Block;
use ActivityPub\Type\Extended\Activity\Create;
use ActivityPub\Type\Extended\Activity\Delete;
use ActivityPub\Type\Extended\Activity\Dislike;
use ActivityPub\Type\Extended\Activity\Flag;
use ActivityPub\Type\Extended\Activity\Follow;
use ActivityPub\Type\Extended\Activity\Ignore;
use ActivityPub\Type\Extended\Activity\Invite;
use ActivityPub\Type\Extended\Activity\Join;
use ActivityPub\Type\Extended\Activity\Leave;
use ActivityPub\Type\Extended\Activity\Like;
use ActivityPub\Type\Extended\Activity\Listen;
use ActivityPub\Type\Extended\Activity\Move;
use ActivityPub\Type\Extended\Activity\Offer;
use ActivityPub\Type\Extended\Activity\Question;
use ActivityPub\Type\Extended\Activity\Read;
use ActivityPub\Type\Extended\Activity\Reject;
use ActivityPub\Type\Extended\Activity\Remove;
use ActivityPub\Type\Extended\Activity\TentativeAccept;
use ActivityPub\Type\Extended\Activity\TentativeReject;
use ActivityPub\Type\Extended\Activity\Travel;
use ActivityPub\Type\Extended\Activity\Undo;
use ActivityPub\Type\Extended\Activity\Update;
use ActivityPub\Type\Extended\Activity\View;
use ActivityPub\Type\Extended\Object\Article;
use ActivityPub\Type\Extended\Object\Audio;
use ActivityPub\Type\Extended\Object\Document;
use ActivityPub\Type\Extended\Object\Event;
use ActivityPub\Type\Extended\Object\Image;
use ActivityPub\Type\Extended\Object\Mention;
use ActivityPub\Type\Extended\Object\Note;
use ActivityPub\Type\Extended\Object\Page;
use ActivityPub\Type\Extended\Object\Place;
use ActivityPub\Type\Extended\Object\Profile;
use ActivityPub\Type\Extended\Object\Relationship;
use ActivityPub\Type\Extended\Object\Tombstone;
use ActivityPub\Type\Extended\Object\Video;
use PHPUnit\Framework\TestCase;


class ValidatorHelperTest extends TestCase
{
	/**
	 * Valid scenarios provider
	 */
	public function getValidAttributesScenarios()
	{
		# TypeClass, property, value
		return [
			[Activity::class, 'actor', 'https://example.com/bob'	], # Set actor as URL
			[ObjectType::class, 'id', 'https://example.com'			], # Set an URL as id  
			[Place::class, 'accuracy', 100							], # Set accuracy (int) 
			[Place::class, 'accuracy', 0							], # Set accuracy (int)
			[Place::class, 'accuracy', '0'							], # Set accuracy (numeric int) 
			[Place::class, 'accuracy', '0.5'						], # Set accuracy (numeric float) 
		];
	}

	/**
	 * Exception scenarios provider
	 */
	public function getExceptionScenarios()
	{
		# TypeClass, property, value
		return [
			[Activity::class, 'actor', 'https:/example.com/bob'		], # Set actor as malformed URL
			[Activity::class, 'actor', 'bob'						], # Set actor as not allowed string
			[ObjectType::class, 'id', '1'							], # Set a number as id   (should pass @todo type resolver)
			[ObjectType::class, 'id', []							], # Set an array as id
			[Place::class, 'accuracy', -10							], # Set accuracy with a negative int
			[Place::class, 'accuracy', -0.0000001					], # Set accuracy with a negative float
			[Place::class, 'accuracy', 'A0.0000001'					], # Set accuracy with a non numeric value
			[Place::class, 'accuracy', 100.000001					], # Set accuracy with a float value out of range
		];
	}

	/**
	 * Check that all core objects have a correct type property.
	 * It checks that getter is working well too.
	 * 
	 * @dataProvider      getValidAttributesScenarios
	 */
	public function testValidAttributesScenarios($type, $attr, $value)
	{
		$object = new $type();
		$object->{$attr} = $value;
		$this->assertEquals($value, $object->{$attr});
	}
	
	/**
	 * @dataProvider      getExceptionScenarios
	 * @expectedException \Exception
	 */
	public function testExceptionScenarios($type, $attr, $value)
	{
		$object = new $type();
		$object->{$attr} = $value;
	}
}
