<?php

namespace Marello\Bundle\PurchaseOrderBundle\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NoteBundle\Entity\Note;

class NoteActivityProcessor
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * NoteActivityProcessor constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $entity
     * @param $updatedItems
     * @param array $data
     * @throws \Exception
     */
    public function addNote($entity, $updatedItems, array $data = [])
    {
        if (!is_object($entity)) {
            throw new \Exception(sprintf('Invalid entity, expected entity but got %s instead', gettype($entity)));
        }
        $message = $this->getMessage($updatedItems);

        if ($message) {
            $note = $this->createNewNote();
            $note->setMessage($message);
            $note->setTarget($entity);
            $this->manager->persist($note);
            $this->manager->flush();
        }
    }

    /**
     * @param $updatedItems
     * @return string
     */
    public function getMessage($updatedItems)
    {
        $message = null;
        foreach ($updatedItems as $k => $item) {
            if (isset($item['item']) && !empty($item['item'])) {
                $message .= sprintf('Product name: %s<br/>', $item['item']->getProductName());
                $message .= sprintf('Quantity Received: %s<br/>', $item['qty']);
                $message .= sprintf('<br/>');
            }
        }

        if ($message) {
            return sprintf('<b>Quantities received for: </b><br/> %s', $message);
        }

        return $message;
    }

    /**
     * Create new note entity
     * @return Note
     */
    private function createNewNote()
    {
        return new Note();
    }
}
