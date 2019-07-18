<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Hostkeys;
use That0n3guy\Transliteration;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7;
use Auth;
use Validator, Image, Session, File, Response, Redirect, Exception;

class HostkeysController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
      try {
        if($request->get('search') != ''){
          $data['data']	= Hostkeys::with('getState')
                                 ->with('getTransition')
                                 ->with('getUserName')
                                 ->where('hostname', 'like', '%'.$request->get('search').'%')
                                 ->orderBy('id', 'desc')
                                 ->paginate(env('PAGINATE', 10));
        } else{
          $data['data']	= Hostkeys::with('getState')
                                 ->with('getTransition')
                                 ->with('getUserName')
                                 ->orderBy('id', 'desc')
                                 ->paginate(env('PAGINATE', 10));
        }
      } catch (Exception $e) {
          $data['data']	= [];
      }
      return view('host_keys.index', $data);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    return view('host_keys.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
      try {
          if($request->keys != NULL){
            $keys = $request->keys;
          }else {
            $keys = "";
          }
          $hostkey = New Hostkeys;
          $hostkey->hostname 	= str_replace(array('https://', 'http://'), array('',''),$request->hostname);
          $hostkey->keys 		= $keys;
          $hostkey->state 		= $request->state;
          $hostkey->transition 	= $request->transition;
          $hostkey->user_id     = $request->user_id;
          $hostkey->save();

          $error = false;
          $statusCode = 200;
          $title = 'Success';
          $type = 'success';
          $message = 'Data Saved Successfuly.';
          $result = $hostkey;
      } catch (Exception $e) {
          $error = true;
          $statusCode = 404;
          $title = 'Error';
          $type = 'error';
          $message = 'Error';
          $result = 'Not Found';
      } finally {
          return Response::json(array(
            'error' => $error,
            'status' => $statusCode,
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'result' => $result
          ));
      }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {

  }

  public function get($hostname)
  {
      try {
          $data	= Hostkeys::with('getState')
                                 ->with('getTransition')
                                 ->with('getUserName')
                                 ->where('hostname', 'like', '%'.$hostname.'%')
                                 ->orderBy('id', 'desc')
                                 ->first();

           $error = false;
           $statusCode = 200;
           $title = 'Success';
           $type = 'success';
           $message = 'Success';
           $result = $data->hostname;
           $resultid = $data->id;
      } catch (Exception $e) {
          $error = true;
          $statusCode = 404;
          $title = 'Error';
          $type = 'error';
          $message = 'Error';
          $result = 'Not Found';
          $resultid = 'Not Found';
      } finally {
          return Response::json(array(
            'error' => $error,
            'status' => $statusCode,
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'result' => $result,
            'id' => $resultid
          ));
      }
  }

  public function request(Request $request){
      Validator::extend('without_spaces', function($attr, $value){
        return preg_match('/^\S*$/u', $value);
      });
      $validator = Validator::make($request->all(), [
        'host'			=> 'required|without_spaces|unique:host_keys,hostname',
        'description'		=> 'required',
      ]);
      if($validator->fails())
      {
        Session::flash('message', 'Please fix the error(s) below');
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
      }
      if(Auth::guest()){ $current_user = 1; }
      else{ $current_user = (Auth::user())?((Auth::user()->id)?Auth::user()->id:1):1; }
      $headers = ['Content-Type' => 'application/json'];
      $data = [
        'host' => $request->host,
        'client' => $request->client,
        'request' => 'Request',
        'deskripsi' => $request->description,
        'user_id' => $current_user
      ];
      $body = json_encode($data);
      $host 			= str_replace(array('https://', 'http://'), array('',''),$request->host);

      try {
          $url = "https://".$host."/api/v1/api-manager/request";
          $client = new \GuzzleHttp\Client();
          $res = $client->request('POST', $url,['headers'=>$headers,'body'=>$body]);
          $response = $res->getBody();
          $responses = json_decode($response);
          $msg = "success";
      } catch (GuzzleException $e) {
          $msg = "error";
          $responses = $e;
      }

      if($msg == "success"){
        $responses = $responses;
      }else {
        try {
            $urlz = "http://".$host."/api/v1/api-manager/request";
            $clientz = new \GuzzleHttp\Client();
            $resz = $clientz->request('POST', $urlz,['headers'=>$headers,'body'=>$body]);
            $responsez = $resz->getBody();
            $responsesz = json_decode($responsez);
            $msgz = "success";
        } catch (GuzzleException $er) {
            $msgz = "error";
            $responsesz = $er;
        }

        if($msgz == "success"){
          $responses = $responsesz;
        }else {
          $message = $msgz.' - '.$responsesz->getMessage();
          Session::flash('message', $message);
          return Redirect::to('host-keys');
        }
      }

      if($responses->status == 200){
		$hostkey = New Hostkeys;
		$hostkey->hostname 		= $host;
		$hostkey->keys 			= "";
		$hostkey->state 		= $responses->result->request;
		$hostkey->transition 	= "Propose To ".$responses->result->request;
		$hostkey->user_id       = $responses->result->user_id;
        $hostkey->save();
        if(env('URL_APIMANAGER') != NULL){
          $url_apimanager = str_replace('"', '',env('URL_APIMANAGER'));
          if($url_apimanager != "" || $url_apimanager != NULL || $url_apimanager != false || !empty($url_apimanager)){
            $this->send_apimanager($url_apimanager,$request,$current_user,$hostkey->transition);
          }
        }
        Session::flash('message', 'Send Request Api Keys Successfuly');
      }else {
        Session::flash('message', $responses->message);
      }

      return Redirect::to('host-keys');
  }

  private function send_apimanager($url_apimanager,$request,$current_user,$keterangan){
      $headers = ['Content-Type' => 'application/json'];
      $host       = str_replace(array('https://', 'http://'), array('',''),$request->host);
      $client       = str_replace(array('https://', 'http://'), array('',''),$request->client);
      $data = [
        'host' => $host,
        'client' => $client,
        'keterangan' => $keterangan,
        'user_id' => $current_user
      ];
      $body = json_encode($data);
      $url_apimanager       = str_replace(array('https://', 'http://'), array('',''),$url_apimanager);

      try {
          $url = "https://".$url_apimanager."/api/store";
          $client = new \GuzzleHttp\Client();
          $res = $client->request('POST', $url,['headers'=>$headers,'body'=>$body]);
          $response = $res->getBody();
          $responses = json_decode($response);
          $msg = "success";
      } catch (GuzzleException $e) {
          $msg = "error";
          $responses = $e;
      }

      if($msg == "success"){
        $responses = $responses;
        $message = 'Send Apikey to Host Api Manager successfully with description is '.$keterangan;
      }else {
        try {
            $urlz = "http://".$url_apimanager."/api/store";
            $clientz = new \GuzzleHttp\Client();
            $resz = $clientz->request('POST', $urlz,['headers'=>$headers,'body'=>$body]);
            $responsez = $resz->getBody();
            $responsesz = json_decode($responsez);
            $msgz = "success";
        } catch (GuzzleException $er) {
            $msgz = "error";
            $responsesz = $er;
        }

        if($msgz == "success"){
          $responses = $responsesz;
          $message = 'Send Apikey to Host Api Manager successfully with description is '.$keterangan;
        }else {
          $responses = $responsesz->getMessage();
          $message = $msgz.' - '.$responsesz->getMessage();
        }
      }

      return $responses;
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {

  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
   public function update(Request $request, $id)
   {
       try {
           if($request->keys != NULL){
             $keys = $request->keys;
           }else {
             $keys = "";
           }
           $hostkey = Hostkeys::findOrFail($id);
           $hostkey->hostname 	= str_replace(array('https://', 'http://'), array('',''),$request->hostname);
           $hostkey->keys 		= $keys;
           $hostkey->state 		= $request->state;
           $hostkey->transition = $request->transition;
           $hostkey->user_id    = $request->user_id;
           $hostkey->save();

           $error = false;
           $statusCode = 200;
           $title = 'Success';
           $type = 'success';
           $message = 'Data Saved Successfuly.';
           $result = $hostkey;
       } catch (Exception $e) {
           $error = true;
           $statusCode = 404;
           $title = 'Error';
           $type = 'error';
           $message = 'Error';
           $result = 'Not Found';
       } finally {
           return Response::json(array(
             'error' => $error,
             'status' => $statusCode,
             'title' => $title,
             'type' => $type,
             'message' => $message,
             'result' => $result
           ));
       }
   }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {

  }

}

?>
