<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PesanBuku email verify</title>        
        <link rel="icon"  href="/images/LogoLKS.png">
        <!-- Include Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class=" bg-gradient-to-t from-yellow-100 to-white">
        <div class="antialiased bg-gray-50 m-8 lg:m-20 ">
            
            <main class="text-center flex flex-col justify-center items-center h-screen border-yellow-500 border-2 border-r-8 border-b-8 rounded">
                    @if ($success)
                        <svg class="checkmark w-40 h-40 text-green-500" viewBox="-2 -2 57 57" fill="none" stroke="currentColor" stroke-width="5">
                            <circle cx="26" cy="26" r="25" fill="none" />
                            <path d="M14 27 L22 35 L38 17" fill="none" stroke-linecap="round" stroke-linejoin="round" class="check" />
                        </svg>
                    @else
                        <svg class="w-40 h-40 text-red-500" viewBox="0 0 52 52" fill="none" stroke="currentColor" stroke-width="5">
                            <line x1="15" y1="15" x2="37" y2="37" class="x-line" stroke-linecap="round" />
                            <line x1="37" y1="15" x2="15" y2="37" class="x-line" stroke-linecap="round" />
                        </svg>
                    @endif

                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 ">
                    <h1 class="sm:text-xl lg:text-3xl">{{$message}}</h1>
                </div>
                <p class="text-gray-500">you may now close this window</p>
            </main>
        </div>
    </body>
    
<style>
  .check {
    stroke-dasharray: 40;
    stroke-dashoffset: 40;
    animation: draw-check 0.5s ease-out forwards;
    animation-delay: 0.5s;
  }

  @keyframes draw-check {
    to {
      stroke-dashoffset: 0;
    }
  }
  .x-line {
        stroke-dasharray: 30;
        stroke-dashoffset: 30;
        animation: draw-x 0.5s ease-out forwards;
    }
    @keyframes draw-x {
        to {
        stroke-dashoffset: 0;
        }
    }
</style>
</html>