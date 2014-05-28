<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/session.php");
define("ERR_PARAMETER", -1);
define("ERR_PERMISSION", -2);
define("SUCCESS", 0);
class Api
{
  private $session;
  private $parameter;
  function __construct(array $parameter)
  {
    $this->session = new Session();
    $this->parameter = $parameter;
  }
  function checkParameter($key)
  {
    if (array_key_exists($key, $this->parameter))
      return true;
    else
    {
      $this->returnError(ERR_PARAMETER, "Incorrect parameter: " . $key);
    }
  }
  function param($key)
  {
    return $this->parameter[$key];
  }
  function getSession()
  {
    return $this->session;
  }
  function returnPermissionDenied()
  {
    $this->returnError(ERR_PERMISSION, "Permission denied");
  }
  function returnCustomError($code, $status)
  {
    $this->returnError(- (1000 + $code), $status);
  }
  function returnSuccess($payload = null)
  {
    $output = array();
    $output["code"] = SUCCESS;
    if ($payload)
    {
      foreach ($payload as $key => $value)
      {
        $output[$key] = $value;
      }
    }
    echo json_encode($output);
    exit(0);
  }
  private function returnError($code, $status)
  {
    $output = array();
    $output["code"] = $code;
    $output["status"] = $status;
    echo json_encode($output);
    exit(0);
  }
}
?>