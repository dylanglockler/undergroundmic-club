<x-mail::message>
# Hey {{ $guest->name }}!

{!! nl2br(e($body)) !!}

Thanks,<br>
**The Underground Mic**
</x-mail::message>
