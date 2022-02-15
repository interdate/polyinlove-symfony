<?php

namespace AppBundle\SonataAdmin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;


class ChildrenAdmin extends AbstractAdmin
{
    public $supportsPreviewMode = true;

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('name', 'text');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) :void
    {
        $datagridMapper->add('name');
    }

    protected function configureListFields(ListMapper $listMapper) :void
    {
        $listMapper->addIdentifier('name');
    }
}
