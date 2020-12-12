<a href="{{ $baseUrl }}wiki/User_talk:{{ $target }}"
   class="btn btn-secondary">
    User talk
</a>

<a href="{{ $baseUrl }}wiki/Special:Contributions/{{ $target }}"
   class="btn btn-light">
    Contribs
</a>

<a href="{{ $baseUrl }}wiki/Special:BlockList/{{ $target }}"
   class="btn btn-light">
    Find block
</a>

<a href="{{ $baseUrl }}w/index.php?title=Special:Log/block&page=User:{{ $target }}"
   class="btn btn-light">
    Block log
</a>

<a href="https://meta.wikimedia.org/wiki/Special:CentralAuth?target={{ $target }}"
   class="btn btn-light">
    Global (b)locks
</a>

@if($canUnblock)
    <a href="{{ $baseUrl }}wiki/Special:Unblock/{{ $target }}"
       class="btn btn-warning">
        Unblock
    </a>
@endif
