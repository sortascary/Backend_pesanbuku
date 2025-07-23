<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PesanBuku Reset</title>        
        <link rel="icon"  href="/images/LogoLKS.png">
        <!-- Include Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        @if ($isValid) 
            <script>
                redirectButton =  document.getElementById('redirectBtn');

                window.onload = function () {
                    window.location.href = "{!! $appUrl !!}";

                    setTimeout(function () {
                        document.getElementById('fallback').style.display = 'block';
                    }, 3000);
                }            

                redirectButton.addEventListener('click', function () {
                    window.location.href = "{!! $appUrl !!}";

                    setTimeout(function () {
                        document.getElementById('fallback').style.display = 'block';
                    }, 3000);
                });
            </script>
        @endif
    </head>
    <body class=" bg-gradient-to-t from-yellow-100 to-white">
        <div class="antialiased bg-gray-50 m-8 lg:m-20 ">
            
            <main class="text-center flex flex-col justify-center items-center h-screen border-yellow-500 border-2 border-r-8 border-b-8 rounded">

                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 ">
                    <h1 class="sm:text-xl lg:text-3xl">{{$isValid? 'Redirecting to the app.' : 'Invalid or expired token.'}}</h1>                    
                </div>
                @if ($isValid)                
                    <button id="redirectBtn" 
                            class="redirectBtn"
                            type="button inline-flex items-center"
                            >
                            Open in app
                        </button>
                    <p class="text-gray-500 mt-4">
                        If you were not redirected, click the button or open this link manually:<br>
                        <a href="{!! $appUrl !!}" class="text-blue-500 underline">{!! $appUrl !!}</a>
                    </p>
                @endif
            </main>
        </div>
    </body>
    
</html>