<?php

  $strLog       	= "";
  $strBody      	= file_get_contents('php://input');
  $strToken     	= $_POST["token"];
  $strYourBusinessName	= ""; // Fill in your Kinde BusinessName

/*

DATA Example

const headers = {
  'Accept':'application/json',
  'Authorization':'Bearer {access-token}'
};

fetch('https://{businessName}.kinde.com/oauth2/user_profile',
{
  method: 'GET',
  headers: headers
})

Delivers: 200

{
  "id": "string",
  "preferred_email": "string",
  "provided_id": "string",
  "last_name": "string",
  "first_name": "string",
  "picture": "string"
}

*/

  // Create the header
  $arrHeaders   = array ();
  $arrHeaders[] = "Accept:application/json";
  $arrHeaders[] = "Authorization:Bearer " . $strToken;

  // Empty Body
  $strBody      = "";
  $strResult    = "";

  $blnRes       = PostUrl ( "https://" . $strYourBusinessName . ".kinde.com/oauth2/user_profile",
                            $strBody,
                            $strResult,
                            $arrHeaders
                          );

  // Only log it if something fails
  if ( ! $blnRes ) {
    $strLog       = "
PostUrl gave exit code:'" . $blnRes . "'
result:$strResult
";
    file_put_contents ("/tmp/proxy.log", $strLog, FILE_APPEND );
  }

  die ( $strResult );


// Simple Helper function to call curl
function PostUrl ( $strUrl = "",
                   $strData = "",
                   &$result = "",
                   $arrHeaders = array()
                 )
{
  $blnRes                                       = false;

  // Init Curl
  $ch                                           = curl_init( $strUrl );

  // Set needed uptions, play with VERBOSE && HEADER to see more detail if needed
  curl_setopt($ch, CURLOPT_URL,                 $strUrl );
  curl_setopt($ch, CURLOPT_FRESH_CONNECT,       1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,      1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION,      1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,      0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,      0);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,      0);
  curl_setopt($ch, CURLOPT_VERBOSE,             0); // 1
  curl_setopt($ch, CURLOPT_HEADER,              0); // 1
  curl_setopt($ch, CURLOPT_POST,                1);

  // feed the data
  curl_setopt($ch, CURLOPT_HTTPHEADER,          $arrHeaders );
  curl_setopt($ch, CURLOPT_POSTFIELDS,          $strData );


  // Now run it
  $result                                       = curl_exec($ch);
  $http_code                                    = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

  if ( ! curl_errno($ch) ) {
    $arrCurlInfo = curl_getinfo($ch);

    switch ( $http_code ) {
      case 200:  # OK
        $blnRes = true;
        break;
      default:
        $result = "ERROR: recieved httpcode code:" . $http_code . ", result:'".$result."'";
    }
  } else {
    $strCurlErr = curl_error($ch);
    $result	= "ERROR: recieved httpcode code:" . $http_code . ", recieved CURL error:" . $strCurlErr . ", output result:'".$result."'";
  }

  // Close it
  curl_close($ch);

  // Return the ExitCode
  return $blnRes;
}

