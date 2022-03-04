@for($i = 1; $i<= $stars; $i++)
    <img src="/img/starfull.ico">
@endfor
{{-- half star --}}
@if(($stars - round($stars, 0)) != 0)
    <img src="/img/hstar.ico">
@endif
@for($i = 1; $i<= 5 -$stars; $i++)
    <img src="/img/star.ico">
@endfor