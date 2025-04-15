<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-plus"></i> Team Fortress 2 - Add Privileges</h2>
		<form method="POST" action="/tf2/add-privileges">
			<label for="steamid"><i class="fas fa-id-card"></i> SteamID:</label>
			<input type="text" name="steamid" id="steamid" placeholder="STEAM_0:0:123456789" maxlength="32" required>

			<label for="name"><i class="fas fa-user"></i> Username:</label>
			<input type="text" id="name" name="name"  maxlength="32" required>

			<label for="privileges"><i class="fas fa-key"></i> Privileges:</label>
			<select id="privileges" name="privileges" required>
				<option value="a">VIP</option>
				<option value="abcdefgjk">STAFF</option>
			</select>

			<label for="immunity"><i class="fas fa-shield-alt"></i> Immunity:</label>
			<select id="immunity" name="immunity" required>
				<option value="0">VIP (0)</option>
				<option value="10">TF2 Admin (10)</option>
				<option value="20">Moderator (20)</option>
				<option value="30">Senior Moderator (30)</option>
			</select>

			<label for="comment"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" name="comment" id="comment" placeholder="Optional" maxlength="50">

			<label for="discord"><i class="fab fa-discord"></i> Discord:</label>
			<input type="text" id="discord" name="discord" placeholder="Optional" maxlength="50">

			<label for="days"><i class="fas fa-calendar-alt"></i> Duration:</label>
			<select id="days" name="days" required>
				<option value="7">7 days</option>
				<option value="14">14 days</option>
				<option value="21">21 days</option>
				<option value="30">30 days</option>
				<option value="90">90 days</option>
				<option value="180">180 days</option>
				<option value="365">1 Year</option>
				<option value="lifetime">Lifetime</option>
			</select>

			<input type="submit" value="Add Privileges" class="button system">
		</form>

		<?php echo $error ?? ''; ?>
		<?php echo $success ?? ''; ?>
	</div>
</body>
</html>
