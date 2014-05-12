<?php

namespace Sokil\Mongo;

class Queue implements \Countable
{
    /**
     *
     * @var \Sokil\Mongo\Collection
     */
    private $_collection;
    
    public function __construct(Database $database, $channel)
    {
        $this->_collection = $database->getCollection($channel);
    }
    
    /**
     * 
     * @param array $payload data to send
     * @param int $priority more priority num give quicker geting from queue
     */
    public function enqueue(array $payload, $priority = 0)
    {
        $this->_collection
            ->createDocument(array(
                'payload'   => $payload,
                'priority'  => (int) $priority,
                'datetime'  => new \MongoDate,
            ))
            ->save();
        
        return $this;
    }
    
    public function dequeueArray()
    {
        $document = $this->_collection
            ->find()
            ->sort(array(
                'priority' => -1,
                'datetime' => 1,
            ))
            ->findAndRemove();
        
        if(!$document) {
            return null;
        }
        
        return $document->get('payload');
    }
    
    public function dequeue()
    {
        return new Structure($this->dequeueArray());
    }
    
    public function count()
    {
        return count($this->_collection);
    }
    
    public function clear()
    {
        $this->_collection->delete();
        return $this;
    }
}