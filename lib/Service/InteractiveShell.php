<?php
declare(strict_types=1);


/**
 * Some small tools for Nextcloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018, Maxence Lange <maxence@artificial-owl.com>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace daita\NcSmallPhpTools\Service;


use daita\NcSmallPhpTools\Exceptions\ShellMissingItemException;
use daita\NcSmallPhpTools\Exceptions\ShellUnknownCommandException;
use daita\NcSmallPhpTools\Exceptions\ShellUnknownItemException;
use daita\NcSmallPhpTools\IInteractiveShellClient;
use daita\NcSmallPhpTools\Traits\TStringTools;
use OC\Core\Command\Base;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


/**
 * Class CommandShellService
 *
 * @package daita\NcSmallPhpTools\Service
 */
class InteractiveShell {


	use TStringTools;


	/** @var Base */
	private $parent;

	/** @var InputInterface */
	private $input;

	/** @var OutputInterface */
	private $output;

	/** @var IInteractiveShellClient */
	private $client;


	/** @var QuestionHelper */
	private $helper;

	/** @var array */
	private $commands = [];


	public function __construct(
		Base $parent, InputInterface $input, OutputInterface $output,
		IInteractiveShellClient $client
	) {
		$this->helper = $parent->getHelper('question');
		$this->parent = $parent;
		$this->input = $input;
		$this->output = $output;

		$this->client = $client;
	}


	/**
	 * @param array $commands
	 */
	public function setCommands(array $commands): void {
		$this->commands = array_merge($commands, ['quit', 'help']);
	}


	/**
	 * @param string $prompt
	 */
	public function run(string $prompt = '%PATH%>') {

		$path = '';
		while (true) {
			$question = new Question(str_replace('%PATH%', $path, $prompt), '');

			$commands = $this->availableCommands($path);

			$question->setAutocompleterValues($commands);
			$current = $this->helper->ask($this->input, $this->output, $question);
			if ($current === 'quit' || $current === 'q' || $current === 'exit') {
				exit();
			}

			if ($current === '?' || $current === 'help') {
				$this->listCurrentAvailableCommands($commands);
				continue;
			}

			if ($current === '') {
				$path = '';
			}

			$command = ($path === '') ? $current : str_replace('.', ' ', $path) . ' ' . $current;

			try {
				$this->client->manageCommand($command);
			} catch (ShellMissingItemException $e) {
				foreach ($this->commands as $cmd) {
					$tmp = trim($this->commonPart(str_replace(' ', '.', $command), $cmd), '.');
					if (strlen($tmp) > strlen($path)) {
						$path = $tmp;
					}
				}
			} catch (ShellUnknownItemException $e) {
				$this->output->writeln('<comment>' . $command . '</comment>: command not found');
			} catch (ShellUnknownCommandException $e) {
				$this->output->writeln('<comment>Use \'?\' to list available commands</comment>');
			}
//			$this->output->writeln('');
		}
	}


	/**
	 * @param string $path
	 *
	 * @return string[]
	 */
	private function availableCommands(string $path = ''): array {

		$commands = [];
		foreach ($this->commands as $entry) {
			if ($path !== '' && strpos($entry, $path) === false) {
				continue;
			}

			$subPath = explode('.', $path);
			$list = explode('.', $entry);

			$root = [''];
			for ($i = 0; $i < sizeof($list); $i++) {
				$sub = $list[$i];
				if ($sub === $subPath[$i]) {
					continue;
				}
				$this->parseSubCommand($commands, $root, $sub);
			}
		}

		return $commands;
	}


	/**
	 * @param array $commands
	 */
	private function listCurrentAvailableCommands(array $commands) {
		foreach ($commands as $command) {
			if (strpos($command, ' ') === false) {
				$this->output->writeln('<info>' . $command . '</info>');
			}
		}
	}


	/**
	 * @param array $commands
	 * @param array $root
	 * @param string $sub
	 */
	private function parseSubCommand(array &$commands, array &$root, string $sub) {

		if (substr($sub, 0, 1) === '?') {
			list($source, $field) = explode('_', substr($sub, 1));
			$list = $this->client->fillCommandList($source, $field);
		} else {
			$list = [$sub];
		}

		$newRoot = [];
		foreach ($list as $sub) {
			foreach ($root as $r) {
				$command = ($r === '') ? $sub : $r . ' ' . $sub;
				if (!in_array($command, $commands)) {
					$commands[] = $command;
				}

				$newRoot[] = $command;
			}

		}

		$root = $newRoot;
	}


}
