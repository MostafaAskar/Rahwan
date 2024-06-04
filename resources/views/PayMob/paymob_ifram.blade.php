<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
   
    {{-- <iframe
  width="100%"
  height="800"
  src="https://accept.paymob.com/api/acceptance/iframes/847465?payment_token={{$token}}"
>
</iframe> --}}

<div class="container py-5 text-center">
  <form action="{{ route('checkout') }}" method="POST">
      @csrf
      @method('POST')
      {{-- <h1>{{$message}}</h1> --}}
          <button class="btn btn-lg text-light bg-dark text-center"
              type="submit" >Confirm Online Order
          </button>
  </form>
  </div>
</body>
</html>