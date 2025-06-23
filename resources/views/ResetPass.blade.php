<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PesanBuku Reset</title>        
        <link rel="icon"  href="/images/LogoLKS.png">
        <!-- Include Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <script>
            window.onload = function () {
                // Attempt to open app
                window.location.href = "{!! $appUrl !!}";

                // Optional: fallback timeout if app isn't installed
                setTimeout(function () {
                    window.location.href = "https://yourdomain.com/reset-done";
                }, 3000);
            }
        </script>
    </head>
    <body class=" bg-gradient-to-t from-yellow-100 to-white">
        <div class="antialiased bg-gray-50 m-8 lg:m-20 ">
            
            <main class="text-center flex flex-col justify-center items-center h-screen border-yellow-500 border-2 border-r-8 border-b-8 rounded">

                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 ">
                    <h1 class="sm:text-xl lg:text-3xl">{{$message}}</h1>
                </div>
                <p class="text-gray-500">you may now close this window</p>

            </main>
        </div>
    </body>
    
</html>