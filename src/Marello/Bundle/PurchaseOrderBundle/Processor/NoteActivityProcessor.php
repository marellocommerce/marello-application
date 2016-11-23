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
     */
    public function addNote($entity, $updatedItems, array $data = [])
    {
        $note = new Note();
        $message = $this->getMessage($updatedItems);
        $note->setMessage($message);
        $note->setTarget($entity);
        $this->manager->persist($note);
        $this->manager->flush();
    }

    /**
     * @param $updatedItems
     * @return string
     */
    protected function getMessage($updatedItems)
    {
        $message = null;
        foreach ($updatedItems as $k => $item) {
            $message .= sprintf('Product name: %s<br/>', $item['item']->getProductName());
            $message .= sprintf('Quantity Received: %s<br/>', $item['qty']);
            $message .= sprintf('<br/>');
        }

        return sprintf('<b>Quantities received for: </b><br/> %s', $message);
    }
}
