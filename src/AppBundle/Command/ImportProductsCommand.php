<?php

namespace AppBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductsCommand extends ContainerAwareCommand {

	/**
	 * @var \Symfony\Component\Console\Input\InputArgument
	 */
	protected $input = null;

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output = null;

	/**
	 *
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $documentManager = null;

	protected $result = null;

	protected function configure() {
		$this
			->setName('core:products:import')
			->addOption('import-strategy', null, InputOption::VALUE_REQUIRED)
			->addOption('deserialization-strategy', null, InputOption::VALUE_REQUIRED)
			->addOption('batch-size', null, InputOption::VALUE_REQUIRED)
			->setDescription('Imports Products')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->input = $input;
		$this->output = $output;

		$this->documentManager = $this->getContainer()->get('doctrine_mongodb')->getManager();

		$processStart = microtime(true);
		$this->output->writeln(sprintf('Start process : %s', date('Ymd H:i:s')));

		$result = $this->doExecute();

		$this->output->writeln(sprintf('End process : %s', date('Ymd H:i:s')));
		$processEnd = microtime(true);
		$this->output->writeln(sprintf('Duration : %s seconds.', $processEnd - $processStart));

	}

	protected function doExecute() {

		$persistanceStrategy = $this->input->getOption('import-strategy');
		$deserializationStrategy = $this->input->getOption('deserialization-strategy');

		$batchSize = $this->input->getOption('batch-size');

		$importer = $this->getImporter();

		$importer->ensureValidPersistanceStrategy($persistanceStrategy);
		$importer->ensureValidDeserializationStrategy($deserializationStrategy);

		if (is_numeric($batchSize)) {
			$importer->setBatchSize($batchSize);
		}

		$timeElapsed = $importer->import($persistanceStrategy);

		$this->setResult([
			'persistanceTimeElapsed' => $timeElapsed,
			'deserializationTimeElapsed' => $importer->getDeserializationTime(),
			'strategy' => $persistanceStrategy,
			'memUsage' => memory_get_peak_usage(),
			'batchSize' => $batchSize,
		]);
	}

	protected function getImporter() {
		return $this->getContainer()->get('importer.products');
	}

	/**
	 * @return \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected function getDocumentManager() {
		return $this->documentManager;

	}

	/**
	 *
	 * @return LoggerInterface
	 */
	protected function getLogger() {
		return $this->logger;
	}

	/**
	 * Get result
	 * @return mixed
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Set result
	 * @return $this
	 */
	public function setResult($result) {
		$this->result = $result;
		return $this;
	}
}
