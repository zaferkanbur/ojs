<?php

namespace Ojs\AnalyticsBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * This collections keeps page information and like count *without user info*
 * @MongoDb\Document(collection="analytics_bookmark_object_sum")
 */
class ObjectBookmark extends ObjectView
{
}
