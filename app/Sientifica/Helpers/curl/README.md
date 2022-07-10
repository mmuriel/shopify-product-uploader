# curl
This is a simple cURL library to dispatch HTTP GET, POST, PUT and DELETE request for php language projects. 

## How to use it

1. Import the library:

```
use Sientifica\Curl
```

2. Instantiate a new Sientifica\Curl object:

```
$curl = new Sientifica\Curl();
```

3. Make any http (any http verb) request you want:

### For a HTTP GET request:

```
$response = $curl->urlGet('https://url-to-get');
```

### For a HTTP POST request:

* HTTP POST, no Content-Type defined 

```
		$postData = [
		'element1' => 'Value 1', 
		'element2' => 'Value 2' 
	];

	$response = $curl->urlPost('https://url-to-get',$postData);
```

* HTTP POST, under json format:

```
	$postData = [
		'element1' => 'Value 1', 
		'element2' => 'Value 2' 
	];

	$headers = [
		'Content-Type: application/json'
	];

	$response = $curl->urlPost('https://url-to-get',$postData,$headers);
```

* HTTP POST, under multipart/form-data (as regular html web forms)

```
	$postData = [
		'element1' => 'Value 1', 
		'element2' => 'Value 2' 
	];

	$headers = [
		'Content-Type: multipart/form-data'
	];

	$response = $curl->urlPost('https://url-to-get',$postData,$headers);
```