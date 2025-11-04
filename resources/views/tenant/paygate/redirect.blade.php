<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Redirecting to PayGate...</title>
</head>
<body>
  <p>Redirecting to payment gateway... If you are not redirected, click the button below.</p>

  <form id="paygate_form" method="POST" action="{{ $endpoint }}">
    @foreach($payload as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ is_array($v) ? json_encode($v) : $v }}">
    @endforeach
    <noscript>
        <button type="submit">Pay now</button>
    </noscript>
  </form>

  <script>
    document.getElementById('paygate_form').submit();
  </script>
</body>
</html>