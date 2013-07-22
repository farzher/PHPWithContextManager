PHPWithContentManager
=====================

Python-like context manager for PHP



#Installation

Download [with.php](https://raw.github.com/farzher/PHPWithContentManager/master/with.php) and `require` it.



#Usage

	#!/usr/bin/env php
	<?php

	require('with.php');

	class A {
		public function _enter() {
			echo "A is setting up stuff.\n";
		}

		public function _exit($exception) {
			echo "A is cleaning up stuff.\n";
			if(!empty($exception)) {
				echo "I've handled the exception\n";
				// Return true to say you've handled the exception.
				// This will catch the exception.
				return true;
			}
		}
	}

	class B {
		public function _enter() {
			echo "B is setting up stuff.\n";
			// Return a value to pass it to the closure instead.
			return 1;
		}

		public function _exit($exception) {
			echo "B is cleaning up stuff.\n";
		}
	}

	with(new A, new B, function($a, $b) {
		// $a is an object, $b is the number 1
		var_dump($a, $b);
		throw new Exception('Something went wrong.');
		echo "This should not be seen because of exception.\n";
	});

	// We get here because class A handles the exception.
	echo "After with.\n";


Output:

	A is setting up stuff.
	B is setting up stuff.
	class A#1 (0) {
	}
	int(1)
	A is cleaning up stuff.
	I've handled the exception
	B is cleaning up stuff.
	After with.


If used with resources, they'll be closed when you're done, even if an exception was thrown.

	with(fopen('data.txt', 'r'), function($handle) {
		$data = fread($handle, 10);
	});
