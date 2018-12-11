<?php

namespace ActivityPubTest\Type;

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
use ActivityPub\Type\Validator;
use PHPUnit\Framework\TestCase;

class AttributeFormatValidationTest extends TestCase
{
	/**
	 * Valid scenarios provider
	 */
	public function getValidAttributesScenarios()
	{
		# TypeClass, property, value
		return [
['accuracy', Place::class, 100                                         ], # Set accuracy (int) 
['accuracy', Place::class, 0                                           ], # Set accuracy (int)
['accuracy', Place::class, '0'                                         ], # Set accuracy (numeric int) 
['accuracy', Place::class, '0.5'                                       ], # Set accuracy (numeric float) 
['actor', Activity::class, 'https://example.com/bob'                   ], # Set actor as URL
['actor', Activity::class, '{ "type": "Person",
                              "id": "http://sally.example.org",
                              "summary": "Sally"
                            }'                                         ], # Set actor as an Actor type, JSON encoded
['actor', Activity::class, '[ "http://joe.example.org",
                              {
                                "type": "Person",
                                "id": "http://sally.example.org",
                                "name": "Sally"
                              }
                            ]'                                         ], # Set actor as multiple actors, JSON encoded
['altitude', Place::class, 0.5                                         ], # Set altitude (float)
['anyOf', Question::class, '[
                              {
                                "type": "Note",
                                "name": "Option A"
                              },
                              {
                                "type": "Note",
                                "name": "Option B"
                              }
                            ]'                                         ], # Set anyOf choices 
['attachment', Note::class, '[
                               {
                                 "type": "Image",
                                 "content": "This is what he looks like.",
                                 "url": "http://example.org/cat.jpeg"
                              }
                            ]'                                         ], # Set attachment with an ObjectType
['attachment', Note::class, '[
                              {
                                "type": "Link",
                                "href": "http://example.org/cat.jpeg"
                              }
                            ]'                                         ], # Set attachment with an Link
['attachment', Note::class, '["http://example.org/cat.jpeg"]'          ], # Set attachment with an indirect reference
['attachment', ObjectType::class, '[
                                     {
                                       "type": "Image",
                                       "content": "This is what he looks like.",
                                       "url": "http://example.org/cat.jpeg"
                                     }
                                   ]'                                  ], # Set attachment	
['attributedTo', Image::class, '[
                                  {
                                    "type": "Person",
                                    "name": "Sally"
                                  }
                                ]'                              	   ], # Set attributedTo with an array of persons
['attributedTo', Image::class, '{ "type": "Person",
                                  "name": "Sally" }'                   ], # Set attributedTo with a single actor
['attributedTo', Image::class, '{
                                  "type": "Link",
                                  "href": "http://joe.example.org"}'   ], # Set attributedTo with a Link
['attributedTo', Image::class, '[
                                  "http://sally.example.org",
                                  {
                                    "type": "Person",
                                    "name": "Sally"
                                  }
                                ]'                                     ], # Set attributedTo with an array of mixed URL and persons
['audience', Note::class, '[
                             {
                               "type": "Person",
                               "name": "Sally"
                             }
                           ]'                                          ], # Set audience with an array of persons
['audience', Note::class, '{
                             "type": "Person",
                             "name": "Sally"
                           }'                                          ], # Set audience with a single actor
['audience', Note::class, '{
                             "type": "Link",
                             "href": "http://joe.example.org"
                           }'                                          ], # Set audience with a Link
['audience', Note::class, '[
                             "http://sally.example.org",
                             {
                               "type": "Person",
                               "name": "Sally"
                             }
                           ]'                                          ], # Set attributedTo with an array of mixed URL and persons
['bcc', Offer::class, '["http://sally.example.org",
                        {
                          "type": "Person",
                          "name": "Bob",
                          "url": "http://bob.example.org"
                        }
                       ]'                                              ], # Set bcc with an array of mixed URL and persons
['bto', Offer::class, '["http://joe.example.org",
                        {
                          "type": "Person",
                          "name": "Bob",
                          "url": "http://bob.example.org"
                        }
                       ]'                                              ], # Set bto with an array of mixed URL and persons
['cc', Offer::class, '["http://sally.example.org",
                       {
                         "type": "Person",
                         "name": "Bob",
                         "url": "http://bob.example.org"
                       }
                      ]'                                               ], # Set cc with an array of mixed URL and persons
['closed', Question::class, '2016-05-10T00:00:00Z'                     ], # Set closed as a Datetime
['closed', Question::class, true                                       ], # Set closed as a boolean
['closed', Question::class, 'http://bob.example.org'                   ], # Set closed as a URL
['closed', Question::class, '{
                               "type": "Object",
                               "name": "Bob",
                               "url": "http://bob.example.org"
                             }'                                        ], # Set closed as an object
['closed', Question::class, '{
                               "type": "Link",
                               "href": "http://bob.example.org"
                             }'                                        ], # Set closed as Link
['content', Note::class, 'http://bob.example.org'                      ], # Set a content string
['contentMap', Note::class, '{
                               "en": "A <em>simple</em> note",
                               "es": "Una nota <em>sencilla</em>",
                               "zh-Hans": "一段<em>简单的</em>笔记"
                             }'                                        ], # Set a content map
['context', ObjectType::class, 'http://bob.example.org'                ], # Set context as a URL
['context', ObjectType::class, '{
                                 "type": "Object",
                                 "name": "Bob",
                                 "url": "http://bob.example.org"
                                }'                                     ], # Set context as an object
['context', ObjectType::class, '{
                                 "type": "Link",
                                 "href": "http://bob.example.org"
                                }'                                     ], # Set context as Link

['current', Collection::class, 'http://example.org/collection'         ], # Set current as a URL
['current', OrderedCollection::class, '{
                                        "type": "Link",
                                        "summary": "Most Recent Items",
                                        "href": "http://example.org/collection"
                                       }'                              ], # Set current as Link

['deleted', Tombstone::class, '2016-05-10T00:00:00Z'                   ], # Set deleted as a Datetime
['describes', Profile::class, new ObjectType()                         ], # Set describes as an ObjectType
['describes', Profile::class, new Note()                               ], # Set describes as a Note

['duration', ObjectType::class, 'PT2H'                                 ], # Set duration as short format
['duration', ObjectType::class, 'P5D'                                  ], # Set duration as short format
['duration', Activity::class, 'P5Y0M1DT3H2M12S'                        ], # Set duration as long format

['endTime', ObjectType::class, '2016-05-10T00:00:00Z'                  ], # Set endTime as a Datetime (UTC)
['endTime', ObjectType::class, '2015-01-31T06:00:00-08:00'             ], # Set endTime as a Datetime (TZ)

['endpoints', Person::class, 'http://sally.example.org/endpoints.json' ], # Set endpoints as a string
['endpoints', Person::class, '{
                               "proxyUrl": "http://example.org/proxy.json",
                               "oauthAuthorizationEndpoint": "http://example.org/oauth.json",
                               "oauthTokenEndpoint": "http://example.org/oauth/token.json",
                               "provideClientKey": "http://example.org/provide-client-key.json",
                               "signClientKey": "http://example.org/sign-client-key.json",
                               "sharedInbox": "http://example.org/shared-inbox.json"
                              }'                                       ], # Set endpoints as a mapping

['first', Collection::class, 'http://example.org/collection?page=0'    ], # Set first as a URL
['first', OrderedCollection::class, '{
                                      "type": "Link",
                                      "summary": "First Page",
                                      "href": "http://example.org/collection?page=0"
                                     }'                                ], # Set first as Link
['followers', Person::class, new Collection()                          ], # Set followers as collection
['followers', Person::class, new OrderedCollection()                   ], # Set followers as OrderedCollection
['following', Person::class, new Collection()                          ], # Set following as collection
['following', Person::class, new OrderedCollection()                   ], # Set following as OrderedCollection

['formerType', Tombstone::class, new Note()                            ], # Set formerType as an Note
['formerType', Tombstone::class, '{"type":"Video"}'                    ], # Set formerType as an Video string


['generator', ObjectType::class, new Person()                          ], # Set generator as a Person
['generator', Note::class, '{"type":"Application"}'                    ], # Set generator as an Application string
['generator', Note::class, 'http://example.org/generator'              ], # Set generator as URL
['generator', Note::class, '{
                             "type": "Link",
                             "href": "http://example.org/generator"
                            }'                                         ], # Set generator as Link
['height', Link::class, 42                                             ], # Set height

['href', Link::class, "http://example.org/generator"                   ], # Set href

['hreflang', Link::class, "i-navajo"                                   ], # Set hreflang irregular
['hreflang', Link::class, "en-GB"                                      ], # Set hreflang lang+region
['hreflang', Link::class, "fr"                                         ], # Set hreflang lang
['hreflang', Link::class, "mn-Cyrl-MN"                                 ], # Set hreflang case
['hreflang', Link::class, "mN-cYrL-Mn"                                 ], # Set hreflang icase

['mediaType', Link::class, "application/pdf"                           ], # Set mediaType on a Link
['mediaType', ObjectType::class, "multipart/form-data"                 ], # Set mediaType on an ObjectType
['mediaType', ObjectType::class, "application/xhtml+xml"               ], # Set mediaType
['mediaType', ObjectType::class, "application/*"                       ], # Set mediaType
['mediaType', ObjectType::class, "application/octet-stream"            ], # Set mediaType
['mediaType', ObjectType::class, "application/vnd.mspowerpoint"        ], # Set mediaType

['name', ObjectType::class, "Bob"                                      ], # Set name with a simple string
['name', ObjectType::class, "Bob 123 !:.,\\/"                          ], # Set name with words, digits and special characters
['name', ObjectType::class, "Bob ;§&~|={}[]*-+/%$^@#\"'"               ], # Set name with words, digits and special characters
['name', Link::class, "Bob ;§&~|={}[]*-+/%$^@#\"'"                     ], # Set name with words, digits and special characters on a Link

['nameMap', Link::class, '{
        "en": "Bob ;§&~|={}[]*-+/%$^@#\"\'",
        "es": "Una nota sencilla",
        "zh-Hans": "一段简单的笔记"
    }'                                                                 ], # Set nameMap with words, digits and special characters on a Link

['next', CollectionPage::class, '{
                            "type": "Link",
                            "name": "Next Page",
                            "href": "http://example.org/collection?page=2"
                        }'                                             ], # Set next as a Link
['next', CollectionPage::class, 'http://example.org/collection?page=2' ], # Set next as a URL
['next', CollectionPage::class, new CollectionPage()                   ], # Set next as a CollectionPage
                   
['icon', Note::class, '{
                        "type": "Image",
                        "name": "Note icon",
                        "url": "http://example.org/note.png",
                        "width": 16,
                        "height": 16
                       }'                                              ], # Set icon as  an Image
['icon', Note::class, '[
                        {
                         "type": "Image",
                         "summary": "Note (16x16)",
                         "url": "http://example.org/note1.png",
                         "width": 16,
                         "height": 16
                        },
                        {
                         "type": "Image",
                         "summary": "Note (32x32)",
                         "url": "http://example.org/note2.png",
                         "width": 32,
                         "height": 32
                        }
                       ]'                                              ], # Set icon as an array of Image's
['icon', Note::class, '{
                        "type": "Link",
                        "href": "http://example.org/icon"
                       }'                                              ], # Set icon as Link

['image', Note::class, '{
                         "type": "Image",
                         "name": "A Cat",
                         "url": "http://example.org/cat.png"
                        }'                                             ], # Set image as  an Image
['image', Note::class, '[
                         {
                          "type": "Image",
                          "name": "Cat 1",
                          "url": "http://example.org/cat1.png"
                         },
                         {
                          "type": "Image",
                          "name": "Cat 2",
                          "url": "http://example.org/cat2.png"
                         }
                        ]'                                             ], # Set image as an array of Image's
['image', Note::class, '{
                         "type": "Link",
                         "href": "http://example.org/image"
                        }'                                             ], # Set image as Link

['inbox', Person::class, new OrderedCollection()                       ], # Set inbox as an OrderedCollection
['inbox', Application::class, new OrderedCollectionPage()              ], # Set inbox as an OrderedCollectionPage

['latitude', Place::class, 42                                          ], # Set latitude as an integer
['latitude', Place::class, -42.6                                       ], # Set latitude as a float number
['longitude', Place::class, 92                                         ], # Set longitude as an integer
['longitude', Place::class, -92.6                                      ], # Set longitude as a float number

['oneOf', Question::class, '[
                              {
                                "type": "Note",
                                "name": "Option A"
                              },
                              {
                                "type": "Note",
                                "name": "Option B"
                              }
                            ]'                                         ], # Set oneOf choices 

['partOf', CollectionPage::class, "http://example.org/collection"      ], # Set partOf as URL
['partOf', CollectionPage::class, '{
                         "type": "Link",
                         "href": "http://example.org/image"
                        }'                                             ], # Set partOf as Link
['partOf', CollectionPage::class, new Collection()                     ], # Set partOf as Collection

['outbox', Person::class, new OrderedCollection()                      ], # Set outbox as an OrderedCollection
['outbox', Application::class, new OrderedCollectionPage()             ], # Set outbox as an OrderedCollectionPage

['preferredUsername', Person::class, "My Name"                         ], # Set preferredUsername as a string

['published', ObjectType::class, '2016-05-10T00:00:00Z'                ], # Set published as a Datetime (UTC)
['published', ObjectType::class, '2015-01-31T06:00:00-08:00'           ], # Set published as a Datetime (TZ)

['radius', Place::class, 0                                             ], # Set radius as an integer
['radius', Place::class, 42                                            ], # Set radius as an integer
['radius', Place::class, 42.6                                          ], # Set radius as a float number

['rel', Link::class, ["canonical", "preview"]                          ], # Set rel as an array
['rel', Link::class, "alternate"                                       ], # Set rel as a string

['startIndex', OrderedCollectionPage::class, 0                         ], # Set startIndex as 0
['startIndex', OrderedCollectionPage::class, 42                        ], # Set startIndex as 42

['startTime', ObjectType::class, '2016-05-10T00:00:00Z'                ], # Set startTime as a Datetime (UTC)
['startTime', ObjectType::class, '2015-01-31T06:00:00-08:00'           ], # Set startTime as a Datetime (TZ)

['summary', Application::class, 'A simple <em>note</em>'               ], # Set summary as a string
['summaryMap', Application::class, '{
                                     "en": "A simple <em>note</em>",
                                     "es": "Una <em>nota</em> sencilla",
                                     "zh-Hans": "一段<em>简单的</em>笔记"
                                    }'                                 ], # Set summaryMap as a map

['totalItems', Collection::class, 0                                    ], # Set totalItems as 0
['totalItems', Collection::class, 42                                   ], # Set totalItems as 42

['units', Place::class, 'miles'                                        ], # Set units as a basic units
['units', Place::class, 'http://example.org/my-units'                  ], # Set units as a xsd:anyURI

['updated', ObjectType::class, '2016-05-10T00:00:00Z'                  ], # Set updated as a Datetime (UTC)
['updated', ObjectType::class, '2015-01-31T06:00:00-08:00'             ], # Set updated as a Datetime (TZ)

['url', Note::class, '{
                         "type": "Link",
                         "href": "http://example.org/url"
                      }'                                               ], # Set url as Link
['url', Note::class, [[
                         "type"=> "Link",
                         "href"=> "http://example.org/url"
                      ],
                      "http://example.org/4q-sales-forecast.pdf"]      ], # Set url as an array of Link and URL
['url', Document::class, 'http://example.org/4q-sales-forecast.pdf'    ], # Set url as a URL

['width', Link::class, 42                                              ], # Set width

['id', ObjectType::class, "http://sally.example.org"                   ], # Set an id
		];
	}

/* -------------------------------------------------------------------
 | Exception scenarios
 * -------------------------------------------------------------------*/

	/**
	 * Exception scenarios provider
	 */
	public function getExceptionScenarios()
	{
		# TypeClass, property, value
		return [
['actor', Activity::class, 'https:/example.com/bob'                    ], # Set actor as malformed URL
['actor', Activity::class, 'bob'                                       ], # Set actor as not allowed string
['actor', Activity::class, 42                                          ], # Set actor as not allowed type
['actor', Activity::class, '{}'                                        ], # Set actor as a JSON malformed string
['actor', Activity::class, '[
                             "http://joe.example.org",
                             {
                              "type": "Person",
                              "name": "Sally"
                             }
                            ]'                                         ], # Set actor as multiple actors, JSON encoded, missing id for one actor
['actor', Activity::class, '[
                             "http://joe.example.org",
                             {
                              "type": "Person",
                              "id": "http://",
                              "name": "Sally"
                             }
                            ]'                                         ], # Set actor as multiple actors, JSON encoded, invalid id
['actor', Activity::class, '[
                             "http://",
                             {
                              "type": "Person",
                              "id": "http://joe.example.org",
                              "name": "Sally"
                             }
                            ]'                                         ], # Set actor as multiple actors, JSON encoded, invalid indirect link
['accuracy', Place::class, -10                                         ], # Set accuracy with a negative int
['accuracy', Place::class, -0.0000001                                  ], # Set accuracy with a negative float
['accuracy', Place::class, 'A0.0000001'                                ], # Set accuracy with a non numeric value
['accuracy', Place::class, 100.000001                                  ], # Set accuracy with a float value out of range
['altitude', Place::class, 100                                         ], # Set altitude with an int value
['altitude', Place::class, '100.5'                                     ], # Set altitude with a text value
['altitude', Place::class, 'hello'                                     ], # Set altitude with a text value
['altitude', Place::class, []                                          ], # Set altitude with an array
['anyOf', Place::class, []                                             ], # Set anyOf for an inappropriate type
['anyOf', Question::class, []                                          ], # Set anyOf with an array
['anyOf', Question::class, '[
                             {
                              "type": "Note",
                             },
                             {
                              "type": "Note",
                              "name": "Option B"
                             }
                            ]'                                         ], # Set anyOf with malformed choices 
['anyOf', Question::class, '[
                             {
                              "type": "Note",
                              "name": "Option A"
                             },
                             {
                              "name": "Option B"
                             }
                            ]'                                         ], # Set anyOf with malformed choices 
['anyOf', Question::class, '{
                             "type": "Note",
                             "name": "Option A"
                            }'                                         ], # Set anyOf with malformed choices 
['anyOf', Question::class, '[
                             {
                              "type": "Note",
                              "name": "Option A"
                             },
                             {
                              "type": "Note",
                              "name": ["Option B"]
                             }
                            ]'                                         ], # Set anyOf with malformed choices	
['attachment', Note::class, '[
                              {
                               "type": "Image",
                               "content": "This is what he looks like.",
                              }
                             ]'                                        ], # Set attachment with a missing reference
['attachment', Note::class, '[
                              {
                               "type": "Link",
                               "content": "This is what he looks like.",
                              }
                             ]'                                        ], # Set attachment with a missing reference
['attributedTo', Image::class, '[
                                 {
                                  "type": "Person"
                                 }
                                ]'                                     ], # Set attributedTo with a missing attribute (Array)
['attributedTo', Image::class, '{
                                 "name": "Sally"
                                }'                                     ], # Set attributedTo with a single malformed type
['attributedTo', Image::class, '{
                                 "type": "Link",
                                }'                                     ], # Set attributedTo with a malformed Link
['attributedTo', Image::class, '[
                                 "http://sally.example.org",
                                 {
                                  "type": "Person",
                                 }
                                ]'                                     ], # Set attributedTo with an array of mixed URL and persons (malformed)
['audience', Image::class, '[
                             {
                              "type": "Person"
                             }
                            ]'                                         ], # Set audience with a missing attribute (Array)
['audience', Image::class, '{
                             "name": "Sally"
                            }'                                         ], # Set audience with a single malformed type
['audience', Image::class, '{
                             "type": "Link"
                            }'                                         ], # Set audience with a malformed Link
['audience', Image::class, '[
                             "http://sally.example.org",
                             {
                              "type": "Person",
                             }
                            ]'                                         ], # Set audience with an array of mixed URL and persons (malformed)
['audience', Image::class, 42                                          ], # Set audience with an integer
['audience', Link::class, '["http://sally.example.org"]'               ], # Set audience with on a bad container (Link)
['bcc', Offer::class, '[
                        "http://sally.example.org",
                        {
                         "type": "Person",
                         "name": "Sally"
                        }
                       ]'                                              ], # Set bcc with an array of mixed URL and persons (missing url property)
['bcc', Offer::class, '[
                        "http://sally.example.org",
                        {
                         "type": "Person",
                         "name": "Sally",
                         "url": "Not an URL"
                        }
                       ]'                                              ], # Set bcc with an array of mixed URL and persons (URL property is not valid)
['bcc', Offer::class, '["Not a valid URL"]'                            ], # Set bcc with malformed URL

['bto', Offer::class, '[
                        "http://sally.example.org",
                        {
                         "type": "Person",
                         "name": "Sally"
                        }
                       ]'                                              ], # Set bto with an array of mixed URL and persons (missing url property)
['bto', Offer::class, '[
                        "http://sally.example.org",
                        {
                         "type": "Person",
                         "name": "Sally",
                         "url": "Not an URL"
                        }
                       ]'                                              ], # Set bto with an array of mixed URL and persons (URL property is not valid)
['bto', Offer::class, '["Not a valid URL"]'                            ], # Set bto with malformed URL

['cc', Offer::class, '[
                       "http://sally.example.org",
                       {
                        "type": "Person",
                        "name": "Sally"
                       }
                      ]'                                               ], # Set cc with an array of mixed URL and persons (missing url property)
['cc', Offer::class, '[
                       "http://sally.example.org",
                       {
                        "type": "Person",
                        "name": "Sally",
                        "url": "Not an URL"
                       }
                      ]'                                               ], # Set cc with an array of mixed URL and persons (URL property is not valid)
['cc', Offer::class, '["Not a valid URL"]'                             ], # Set cc with malformed URL

['closed', Question::class, '2016-05-10 00:00:00Z'                     ], # Set closed as a Datetime (malformed)
['closed', Question::class, '2016-05-32T00:00:00Z'                     ], # Set closed as a Datetime (malformed)
['closed', Question::class, 42                                         ], # Set closed as a integer
['closed', Question::class, 'ob.example.org'                           ], # Set closed as a URL (malformed)
['closed', Question::class, '{
                              "type": "BadType",
                              "name": "Bob"
                             }'                                        ], # Set closed as a bad type
['closed', Question::class, '{"type": "Link"}'                         ], # Set closed as a malformed Link
['closed', ObjectType::class, '2016-05-10T00:00:00Z'                   ], # Set closed as a Datetime but on not allowed type

['content', Note::class, []                                            ], # Set a content as array

['contentMap', Note::class, '{
                              "en": "A <em>simple</em> note",
                              "es": "Una nota <em>sencilla</em>",
                              1: "一段<em>简单的</em>笔记"
                             }'                                        ], # Set a content map (bad key)

['contentMap', Note::class, ' { "A <em>simple</em> note"}'             ], # Set a content map (bad key)
['contentMap', Note::class, 'A <em>simple</em> note'                   ], # Set a content map (bad format, string)
['contentMap', Note::class, 42                                         ], # Set a content map (bad format, integer)

['context', ObjectType::class, '1'                                     ], # Set a number as context
['context', ObjectType::class, []                                      ], # Set an array as context
['context', ObjectType::class, '{
                                 "type": "Link"
                                }'                                     ], # Set context as a malformed Link

['current', ObjectType::class, 'http://example.org/collection'         ], # Set current as a URL for a class which is not a subclass of Collection
['current', Collection::class, 'http:/example.org/collection'          ], # Set current as a malformed URL
['current', OrderedCollection::class, '{
                                        "type": "Link",
                                        "summary": "Most Recent Items"
                                       }'                              ], # Set current as Link (malformed)
['current', Collection::class, 42                                      ], # Set current as a bad type value

['deleted', Tombstone::class, '2016-05-10 00:00:00Z'                   ], # Set deleted as a bad Datetime
['deleted', ObjectType::class, '2016-05-10T00:00:00Z'                  ], # Set deleted as a Datetime on a bad Type
['deleted', Tombstone::class, []                                       ], # Set deleted as an array
['deleted', Tombstone::class, 42                                       ], # Set deleted as an integer

['describes', Profile::class, 42                                       ], # Set describes as an integer
['describes', ObjectType::class, 42                                    ], # Set describes on a bad type

['duration', Link::class, 'PT2H'                                       ], # Set duration on a bad type
['duration', ObjectType::class, 'P5DD'                                 ], # Set duration as malformed short format
['duration', Activity::class, 'PY0M1DT3H2M12S'                         ], # Set duration as malformed format
['duration', Activity::class, new Link()                               ], # Set duration as unallowed format

['endTime', ObjectType::class, '2016-05-10 00:00:00Z'                  ], # Set endTime as a bad Datetime
['endTime', Link::class, '2016-05-10 00:00:00Z'                        ], # Set endTime on a bad type
['endTime', ObjectType::class, new ObjectType()                        ], # Set endTime as a bad type

['endpoints', Person::class, 'htt://sally.example.org/endpoints.json'  ], # Set endpoints as a bad url
['endpoints', Person::class, 42                                        ], # Set endpoints with a bad type value
['endpoints', Activity::class,'http://sally.example.org/endpoints.json'], # Set endpoints on a bad type
['endpoints', Person::class, '{
                               "proxyUrl": "http://example.org/proxy.json",
                               "oauthAuthorizationEndpoint": "http://example.org/oauth.json",
                               "oauthTokenEndpoint": "http://example.org/oauth/token.json",
                               "provideClientKey": "http://example.org/provide-client-key.json",
                               "signClientKey": "htp://example.org/sign-client-key.json",
                               "sharedInbox": "http://example.org/shared-inbox.json"
                              }'                                       ], # Set endpoints as a mapping with a malformed URL
['endpoints', Person::class, '{"http://example.org/proxy.json"}'       ], # Set endpoints as a mapping with a malformed key

['first', ObjectType::class, 'http://example.org/collection?page=0'    ], # Set first as a URL for a class which is not a subclass of Collection
['first', Collection::class, 'http:/example.org/collection?page=0'     ], # Set first as a malformed URL
['first', OrderedCollection::class, '{
                                      "type": "Link",
                                      "summary": "First page"
                                     }'                                ], # Set first as Link (malformed)

['first', Collection::class, 42                                        ], # Set first as a bad type value

['followers', Activity::class, new Collection()                        ], # Set followers on a bad container (must be an actor)
['followers', Person::class, new Activity()                            ], # Set followers as a bad type (must be a Collection or an OrderedCollection)
['followers', Person::class, 'http:/example.org/followers'             ], # Set followers as a bad type (@todo should be changed, indirect reference should be supported)

['following', Activity::class, new Collection()                        ], # Set following on a bad container (must be an actor)
['following', Person::class, new Activity()                            ], # Set following as a bad type (must be a Collection or an OrderedCollection)
['following', Person::class, 'http:/example.org/following'             ], # Set following as a bad type (@todo should be changed, indirect reference should be supported)

['formerType', Tombstone::class, 'PoorString'                          ], # Set formerType as a string
['formerType', ObjectType::class, '{"type":"Person"}'                  ], # Set formerType as a Datetime on a bad Type
['formerType', Tombstone::class, '{"type":"Person"}'                   ], # Set formerType as a person
['formerType', Tombstone::class, 42                                    ], # Set formerType as an integer

['generator', Note::class, '{"type":"Activity"}'                       ], # Set generator as an activity
['generator', ObjectType::class, '2016-05-10T00:00:00Z'                ], # Set generator as a Datetime on a bad Type
['generator', Tombstone::class, 42                                     ], # Set generator as an integer
['generator', Link::class, 'http://example.org/generator'              ], # Set generator on a bad type
['generator', ObjectType::class, 'htp://example.org/generator'         ], # Set generator with a bad URL
['generator', Note::class, '{
                             "type": "Link",
                             "href": "htp://example.org/generator"
                            }'                                         ], # Set generator as a malformed Link

['height', ObjectType::class, 42                                       ], # Set height on a bad type
['height', Link::class, 42.5                                           ], # Set height with a bad type
['height', Link::class, 'cat'                                          ], # Set height with a bad type
['height', Link::class, -42                                            ], # Set height with an out of range value

['href', ObjectType::class, "http://example.org/generator"             ], # Set href on a bad type
['href', Link::class, "htp://example.org/generator"                    ], # Set href with a bad URL
['href', Link::class, new Activity()                                   ], # Set href with a bad type

['hreflang', Link::class, "i-navajoK"                                  ], # Set hreflang bad irregular
['hreflang', Activity::class, "en-GB"                                  ], # Set hreflang on a bad type

['mediaType', ObjectType::class, "application/audio-*"                 ], # Set mediaType
['mediaType', ObjectType::class, "application.octet-stream"            ], # Set mediaType
['mediaType', ObjectType::class, "application+vnd.mspowerpoint"        ], # Set mediaType

['name', Link::class, "Bob <span></span>"                              ], # Set name with illegal characters (HTML)
['name', ObjectType::class, "Bob <script></script>"                    ], # Set name with illegal characters (HTML)

['nameMap', Link::class, '{
        "en": "Bob ;§&~|={}[]*-+/%$^@#\"\'",
        "es": "Una nota sencilla",
        "zh-Hans": "<script></script>"
    }'                                                                 ], # Set nameMap with an illegal string (HTML)

['nameMap', Link::class, '{
        "abcdefghijkl": "Bob ;§&~|={}[]*-+/%$^@#\"\'",
        "es": "Una nota sencilla"
    }'                                                                 ], # Set nameMap with an illegal key (Non valid BCP47)

['next', Collection::class, '{
                            "type": "Link",
                            "name": "Next Page",
                            "href": "http://example.org/collection?page=2"
                        }'                                             ], # Set next on a bad type
['next', CollectionPage::class, '{
                            "type": "Link",
                            "name": "Next Page",
                            "href": "htp://example.org/collection?page=2"
                        }'                                             ], # Set next as a malformed Link
['next', CollectionPage::class, 'htp://example.org/collection?page=2'  ], # Set next as a malformed URL
['next', CollectionPage::class, new Collection()                       ], # Set next as a bad type

['icon', Note::class, '{
                        "type": "Imag",
                        "name": "Note icon",
                        "url": "http://example.org/note.png",
                        "width": 16,
                        "height": 16
                       }'                                              ], # Set icon as a bad type
['icon', Note::class, '[
                        {
                         "type": "Imag",
                         "summary": "Note (16x16)",
                         "url": "http://example.org/note1.png",
                         "width": 16,
                         "height": 16
                        },
                        {
                         "type": "Image",
                         "summary": "Note (32x32)",
                         "url": "http://example.org/note2.png",
                         "width": 32,
                         "height": 32
                        }
                       ]'                                              ], # Set icon as an array of Image's
['icon', Note::class, '{
                        "type": "Link"
                       }'                                              ], # Set icon as Link

['image', Note::class, '{
                         "type": "Imag",
                         "name": "Note image",
                         "url": "http://example.org/note.png"
                        }'                                             ], # Set image as a bad type
['image', Note::class, '[
                         {
                          "type": "Imag",
                          "summary": "Note image",
                          "url": "http://example.org/note1.png"
                         },
                         {
                          "type": "Image",
                          "summary": "A cat",
                          "url": "http://example.org/note2.png"
                         }
                        ]'                                             ], # Set image as an array of Image's (bad type)
['image', Note::class, '{
                         "type": "Link"
                        }'                                             ], # Set image as Link (malformed)

['inbox', Activity::class, new OrderedCollection()                     ], # Set inbox on a bad type (Activity)
['inbox', Application::class, new CollectionPage()                     ], # Set inbox as a bad type (Must be an ordered Type)
['inbox', Application::class, 'string'                                 ], # Set inbox as a bad type (Must be a valid object)

['latitude', Place::class, -142                                        ], # Set latitude as an out of range value
['latitude', Place::class, 'Bad Type'                                  ], # Set latitude as a bad type
['latitude', ObjectType::class, 42                                     ], # Set latitude on a bad type

['longitude', Place::class, -182                                       ], # Set longitude as an out of range value
['longitude', Place::class, 'Bad Type'                                 ], # Set longitude as a bad type
['longitude', ObjectType::class, 42                                    ], # Set longitude on a bad type

['oneOf', Place::class, []                                             ], # Set oneOf for an inappropriate type
['oneOf', Question::class, []                                          ], # Set oneOf with an array
['oneOf', Question::class, '[
                             {
                              "type": "Note",
                             },
                             {
                              "type": "Note",
                              "name": "Option B"
                             }
                            ]'                                         ], # Set oneOf with malformed choices 
['oneOf', Question::class, '[
                             {
                              "type": "Note",
                              "name": "Option A"
                             },
                             {
                              "name": "Option B"
                             }
                            ]'                                         ], # Set oneOf with malformed choices 
['oneOf', Question::class, '{
                             "type": "Note",
                             "name": "Option A"
                            }'                                         ], # Set oneOf with malformed choices 
['oneOf', Question::class, '[
                             {
                              "type": "Note",
                              "name": "Option A"
                             },
                             {
                              "type": "Note",
                              "name": ["Option B"]
                             }
                            ]'                                         ], # Set oneOf with malformed choices

['outbox', Activity::class, new OrderedCollection()                    ], # Set outbox on a bad type (Activity)
['outbox', Application::class, new CollectionPage()                    ], # Set outbox as a bad type (Must be an ordered Type)
['outbox', Application::class, 'string'                                ], # Set outbox as a bad type (Must be a valid object)

['partOf', CollectionPage::class, "htp://example.org/collection"       ], # Set partOf as a bad URL
['partOf', CollectionPage::class, '{
                         "type": "Lin",
                         "href": "http://example.org/image"
                        }'                                             ], # Set partOf as as bad Link
['partOf', Collection::class, new Collection()                         ], # Set partOf on a bad type
['partOf', CollectionPage::class, []                                   ], # Set partOf as a bad type

['preferredUsername', Activity::class, 'My name'                       ], # Set preferredUsername on a bad type (Activity)
['preferredUsername', Application::class, new OrderedCollection()      ], # Set preferredUsername as a bad type (OrderedCollection)

['published', ObjectType::class, '2016-05-10 00:00:00Z'                ], # Set published as a bad Datetime
['published', Link::class, '2016-05-10 00:00:00Z'                      ], # Set published on a bad type
['published', ObjectType::class, new ObjectType()                      ], # Set published as a bad type

['radius', Place::class, -182                                          ], # Set radius as an out of range value
['radius', Place::class, 'Bad Type'                                    ], # Set radius as a bad type
['radius', ObjectType::class, 42                                       ], # Set radius on a bad type

['rel', Video::class, ["canonical", "preview"]                         ], # Set rel on a bad type
['rel', Link::class, new Note()                                        ], # Set rel as a bad type
['rel', Link::class, "hello "                                          ], # Set rel as an illegal chain " "
['rel', Link::class, "hello,"                                          ], # Set rel as an illegal chain ,
['rel', Link::class, "hello\n"                                         ], # Set rel as an illegal chain \n
['rel', Link::class, "hello\r"                                         ], # Set rel as an illegal chain \r

['startIndex', ObjectType::class, 0                                    ], # Set startIndex on a bad type
['startIndex', OrderedCollectionPage::class, 42.5                      ], # Set startIndex as a bad type
['startIndex', OrderedCollectionPage::class, -41                       ], # Set startIndex as an out of range value

['startTime', ObjectType::class, '2016-05-10 00:00:00Z'                ], # Set startTime as a bad Datetime
['startTime', Link::class, '2016-05-10 00:00:00Z'                      ], # Set startTime on a bad type
['startTime', ObjectType::class, new ObjectType()                      ], # Set startTime as a bad type

['totalItems', ObjectType::class, 42                                   ], # Set totalItems on a bad type
['totalItems', Collection::class, 42.5                                 ], # Set totalItems with a bad type
['totalItems', Collection::class, 'cat'                                ], # Set totalItems with a bad type
['totalItems', Collection::class, -42                                  ], # Set totalItems with an out of range value

['summary', Link::class, 'A simple <em>note</em>'                      ], # Set summary on a bad type
['summary', ObjectType::class, new Note()                              ], # Set summary as a bad type
['summaryMap', Link::class, '{
                              "en": "A simple <em>note</em>",
                              "es": "Una <em>nota</em> sencilla",
                              "zh-Hans": "一段<em>简单的</em>笔记"
                             }'                                        ], # Set summaryMap on a bad type
['summaryMap', Note::class, 'A simple <em>note</em>'                   ], # Set summaryMap on a bad type

['units', Place::class, 'mile'                                         ], # Set units as a bad units
['units', Place::class, 'htp://example.org/my-units'                   ], # Set units as a bad xsd:anyURI
['units', ObjectType::class, 'miles'                                   ], # Set units on a bad type

['updated', ObjectType::class, '2016-05-10 00:00:00Z'                  ], # Set updated as a bad Datetime
['updated', Link::class, '2016-05-10 00:00:00Z'                        ], # Set updated on a bad type
['updated', ObjectType::class, new ObjectType()                        ], # Set updated as a bad type

['url', Link::class, 'http://example.org/4q-sales-forecast.pdf'        ], # Set url on a bad type
['url', Note::class, '{
                         "type": "Link"
                      }'                                               ], # Set url as malformed Link
['url', Note::class, '{
                         "type": "Link",
                         "href"=> "htp://example.org/url"
                      }'                                               ], # Set url as malformed Link
['url', Note::class, [[
                         "type"=> "Link",
                         "href"=> "http://example.org/url"
                      ],
                      "htp://example.org/4q-sales-forecast.pdf"]       ], # Set url as an array of Link and URL (malformed URL)
['url', Document::class, 'http:/example.org/4q-sales-forecast.pdf'     ], # Set url as a malformed  URL

['width', ObjectType::class, 42                                        ], # Set width on a bad type
['width', Link::class, 42.5                                            ], # Set width with a bad type
['width', Link::class, 'cat'                                           ], # Set width with a bad type
['width', Link::class, -42                                             ], # Set width with an out of range value

['id', ObjectType::class, '1'                                          ], # Set a number as id   (should pass @todo type resolver)
['id', ObjectType::class, []                                           ], # Set an array as id
		];
	}

	/**
	 * Check that all core objects have a correct type property.
	 * It checks that getter is working well too.
	 * 
	 * @dataProvider      getValidAttributesScenarios
	 */
	public function testValidAttributesScenarios($attr, $type, $value)
	{
		$object = new $type();
		$object->{$attr} = $value;
		$this->assertEquals($value, $object->{$attr});
	}
	
	/**
	 * @dataProvider      getExceptionScenarios
	 * @expectedException \Exception
	 */
	public function testExceptionScenarios($attr, $type, $value)
	{
		$object = new $type();
		$object->{$attr} = $value;
	}

	/**
	 * Validator validate() method MUST receive an object as third parameter
	 * 
	 * @expectedException \Exception
	 */
	public function testValidatorValidateContainer()
	{	
		Validator::validate('property', 'value', 'NotAnObject');
	}


	/**
	 * Validator add method MUST receive an object that implements
	 * \ActivityPub\Type\ValidatorInterface interface
	 * 
	 * @expectedException \Exception
	 */
	public function testValidatorAddNotValidCustomValidator()
	{
		Validator::add('custom', new class {
			public function validate($value) {
				return true;
			}
		});
	}
}
