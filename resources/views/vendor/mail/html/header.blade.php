@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php 
    $emailLogo = \App\Models\Setting::get('company_logo'); 
    $companyName = \App\Models\Setting::get('company_name', config('app.name', 'Studio CRM'));
@endphp

@if ($emailLogo)
<img src="{{ asset('storage/' . $emailLogo) }}" class="logo" alt="{{ $companyName }}" style="max-height: 50px; width: auto;">
@else
<span style="font-size: 24px; font-weight: bold; color: #333333;">{!! $companyName !!}</span>
@endif
</a>
</td>
</tr>