{extends file="main.tpl"}

{block name=content}

<div class="pure-menu pure-menu-horizontal bottom-margin">
	<a href="{$conf->action_url}logout"  class="pure-menu-heading pure-menu-link">wyloguj</a>
	<span style="float:right;">użytkownik: {$user->login}, rola: {$user->role}</span>
</div>

<form action="{$conf->action_url}calcCompute" method="post" class="pure-form pure-form-aligned bottom-margin">
	<legend>Kalkulator kredytowy</legend>
	<fieldset>
        <div class="pure-control-group">
			<label for="kwota">kwota</label>
			<input id="kwota" type="text" placeholder="wysokosc kwoty" name="kwota" value="{$form->kwota}">
		</div>
        <div class="pure-control-group">
			<label for="liczba lat">liczba_lat</label>
			<input id="liczba lat" type="text" placeholder="liczba lat" name="liczba lat" value="{$form->liczba_lat}">
		</div>
        <div class="pure-control-group">
			<label for="wysokosc oprocentowania">wysokosc_oprocentowania</label>
			<input id="wysokosc oprocentowania" type="text" placeholder="wysokosc oprocentowania" name="wysokosc oprocentowania" value="{$form->wysokosc_oprocentowania}">
		</div>
		<div class="pure-controls">
			<input type="submit" value="Oblicz kwote raty" class="pure-button pure-button-primary"/>
		</div>
	</fieldset>
</form>	

{include file='messages.tpl'}

{if isset($res->result)}
<div class="messages info">
	Wynik: {$res->result}
</div>
{/if}

{/block}