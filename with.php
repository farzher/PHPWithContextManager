<?php


/**
 * Python-like context manager.
 */
function with() {
	// If we get less than 2 arguments, throw an exception.
	$num_args = func_num_args();
	if($num_args < 2) {
		throw new InvalidArgumentException('Expected at least 2 arguments, got ' . $num_args);
	}

	// Parse the arguments.
	// This just pops the callback off the end of the arguments array.
	$objects = func_get_args();
	$callback = array_pop($objects);
	/**
	 * The objects passed into the with function are not always what's
	 * passed to the callback.
	 * If the object's enter method returns something, we'll pass that in instead.
	 */
	$callback_args = array();

	// Enter
	foreach ($objects as $index => $object) {
		$callback_arg = $object;

		if(method_exists($object, '_enter')) {
			$return = $object->_enter();
			// If the enter method returns something
			// pass that value into the callback, instead of the object.
			if(isset($return)) {
				$callback_arg = $return;
			}
		}

		$callback_args[$index] = $callback_arg;
	}

	// Callback
	// Catch any exception thrown here.
	$exception = null;
	try {
		call_user_func_array($callback, $callback_args);
	} catch(Exception $e) {
		$exception = $e;
	}

	// Exit
	// Allow the exit methods to handle the possible exciption here.
	// Interestingly, we do all of this even if no exception was thrown.

	/**
	 * If anyone handles the exception, set this to true.
	 * If this is false, we'll rethrow the exception after
	 * everyone has exited.
	 */
	$exception_handled = false;

	foreach ($objects as $object) {
		// If the object has an _exit method, call it
		if(method_exists($object, '_exit')) {
			// Pass the exception to the exit method
			$return = $object->_exit($exception);
			if($return === true) {
				$exception_handled = true;
			}
		} else {
			// If the object was a resource, close it
			if(is_resource($object)) {
				fclose($object);
			}
		}
	}

	// If an exception was thrown,
	// and the exception wasn't handled by someone;
	// rethrow the error.
	if(!empty($exception) && !$exception_handled) {
		throw $exception;
	}
}
