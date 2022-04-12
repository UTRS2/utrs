<a href="{{ $baseUrl }}wiki/User_talk:{{ $target }}"
   class="btn btn-secondary">
    {{__('appeals.links.user-talk')}}
</a>

<a href="{{ $baseUrl }}wiki/Special:Contributions/{{ $target }}"
   class="btn btn-light">
    {{__('appeals.links.contribs')}}
</a>

<a href="{{ $baseUrl }}wiki/Special:BlockList/{{ $target }}"
   class="btn btn-light">
    {{__('appeals.links.find-block')}}
</a>

<a href="{{ $baseUrl }}w/index.php?title=Special:Log/block&page=User:{{ $target }}"
   class="btn btn-light">
    {{__('appeals.links.block-log')}}
</a>

<a href="https://meta.wikimedia.org/wiki/Special:CentralAuth?target={{ $target }}"
   class="btn btn-light">
    {{__('appeals.links.ca')}}
</a>

@if($canUnblock)
    <a href="{{ $baseUrl }}wiki/Special:Unblock/{{ $target }}"
       class="btn btn-warning">
        {{__('appeals.links.unblock')}}
    </a>
@endif
