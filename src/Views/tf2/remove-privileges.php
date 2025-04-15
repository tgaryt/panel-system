<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-trash-alt"></i> Team Fortress 2 - Remove Privileges</h2>
		<form method="POST" action="/tf2/remove-privileges">
			<label for="steamid"><i class="fas fa-id-card"></i> SteamID:</label>
			<input type="text" name="steamid" id="steamid" placeholder="STEAM_0:0:123456789" required>

			<input type="submit" value="Remove Privileges" class="button system">
		</form>

		<?php echo $error ?? ''; ?>
		<?php echo $success ?? ''; ?>
</div>
</body>
</html>
