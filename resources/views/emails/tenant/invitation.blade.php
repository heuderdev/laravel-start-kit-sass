@component('mail::message')
# Você foi convidado!

**{{ $tenantName }}** te convidou para participar do workspace com o papel de **{{ ucfirst($role) }}**.

O convite expira em **{{ $expiresAt }}**.

@component('mail::button', ['url' => $acceptUrl, 'color' => 'blue'])
Aceitar Convite
@endcomponent

Se você não esperava este convite, pode ignorar este e-mail.

{{ config('app.name') }}
@endcomponent