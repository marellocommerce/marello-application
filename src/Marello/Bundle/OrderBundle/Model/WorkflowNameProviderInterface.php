<?php

namespace Marello\Bundle\OrderBundle\Model;

interface WorkflowNameProviderInterface
{
    const MARELLO_WORKFLOW_START = 'marello_order';

    const ORDER_WORKFLOW_1 = 'marello_order_b2c_workflow_1';
    const ORDER_WORKFLOW_2 = 'marello_order_b2c_workflow_2';
    const ORDER_POS_WORKFLOW = 'marello_order_pos_workflow';
}
