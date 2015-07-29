<?php
namespace Vmwarephp;

class SoapClient extends \SoapClient {
  private $debug = false;
  private $wsdl;

  public function __construct($wsdl, $options)
  {
    if (isset($options['trace']))
    {
      $this->debug = $options['trace'] ? true : false;
    }

    $this->wsdl = $options['location'];

    parent::__construct($wsdl, $options);
  }


  /**
   * Override the default __soapCall method so that logging can be done
   *
   * @param string $function_name
   * @param array  $arguments
   * @param array  $options
   * @param null   $input_headers
   * @param array  $output_headers
   *
   * @return mixed
   */
  public function __soapCall($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null)
  {
    $return = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);

    if (false && $this->debug)
    {
      $log = new \SoapLog();

      $log['url'] = $this->wsdl;
      $log['function_name'] = $function_name;
      $log['arguments'] = json_encode($arguments, JSON_PRETTY_PRINT);
      $log['options'] = json_encode($options, JSON_PRETTY_PRINT);
      $log['input_headers'] = $this->__getLastRequestHeaders();
      $log['output_headers'] = $this->__getLastResponseHeaders();

      $request = new \DOMDocument('1.0');
      $request->loadXML($this->__getLastRequest());
      $request->preserveWhiteSpace = false;
      $request->formatOutput = true;
      $log['request'] = $request->saveXML();

      $response = new \DOMDocument('1.0');
      $response->loadXML($this->__getLastResponse());
      $response->preserveWhiteSpace = false;
      $response->formatOutput = true;
      $log['response'] = $response->saveXML();

      $log->save();
    }

    return $return;
  }


	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		$request = $this->appendXsiTypeForExtendedDatastructures($request);
		$result = parent::__doRequest($request, $location, $action, $version, $one_way);

		if (isset($this->__soap_fault) && $this->__soap_fault) {
			throw $this->__soap_fault;
		}
		return $result;
	}

	/* PHP does not provide inheritance information for wsdl types so we have to specify that its and xsi:type
	 * php bug #45404
	 * */
	private function appendXsiTypeForExtendedDatastructures($request) {
		return $request = str_replace(array("xsi:type=\"ns1:TraversalSpec\"", '<ns1:selectSet />'), array("xsi:type=\"ns1:TraversalSpec\"", ''), $request);
	}
}
