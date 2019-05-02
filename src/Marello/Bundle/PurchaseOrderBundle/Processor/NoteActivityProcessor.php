<?php

namespace Marello\Bundle\PurchaseOrderBundle\Processor;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\NoteBundle\Entity\Note;

class NoteActivityProcessor
{
    /** @var ObjectManager $manager */
    protected $manager;

    /** @var Note $note */
    protected $note;

    /**
     * NoteActivityProcessor constructor.
     * @param Note $note
     * @param ObjectManager $manager
     */
    public function __construct(
        Note $note,
        ObjectManager $manager
    ) {
        $this->note     = $note;
        $this->manager  = $manager;
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
            $this->note->setMessage($message);
            $this->note->addActivityTarget($entity);
            $this->manager->persist($this->note);
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
}
