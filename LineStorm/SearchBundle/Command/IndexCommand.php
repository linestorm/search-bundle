<?php

namespace LineStorm\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command will re-index all databases
 *
 * Class IndexCommand
 *
 * @package LineStorm\SearchBundle\Command
 */
class IndexCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('linestorm:search:index')
            ->setDescription('Trigger an index build')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $searchManager = $container->get('linestorm.cms.module.search_manager');

        $providers = $searchManager->getSearchProviders();

        foreach($providers as $provider)
        {
            $output->writeln("Indexing {$provider->getModel()}");

            $provider->index();
        }

        $output->writeln("Finished");

    }
}
