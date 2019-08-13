<?php declare(strict_types=1);

namespace Codegen\Samples;

use Codegen\Annotations\Codegen;
use Codegen\Templates\Add\Add;
use Codegen\Templates\Getter\Getter;
use Codegen\Templates\Setter\Setter;

/**
 * @author Matej Bukovsky <matejbukovsky@gmail.com>
 * @Codegen()
 */
class User
{
	/**
	 * @var string
	 * @Getter(type="string")
	 * @Setter(type="string")
	 */
	private $firstName;

	/**
	 * @var string
	 * @Getter(type="string")
	 * @Setter(type="string", default="Bukovsky")
	 */
	private $lastName;

	/**
	 * @var int|null
	 * @Getter(type="int", nullable=true)
	 * @Setter(type="int", nullable=true)
	 */
	private $age;

	/**
	 * @var array
	 * @Add(type="string", allowKey=true)
	 * @Getter(type="array")
	 * @Setter(type="array")
	 */
	private $books;

	public function __construct(string $firstName, string $lastName)
	{
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	}

	public function myUntouchedFunction(int $a): int
	{
		return ++$a;
	}

}