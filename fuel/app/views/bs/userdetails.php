<ul>
	<li>Brukernavn: <?php echo $user['screen_name']; ?></li>
	<li>Navn: <?php echo $user['realname']; ?></li>
	<li>E-post: <?php echo $user['email']; ?></li>
	<li>Grupper:
		<ul>
			<?php
			if (count($user['groups']) == 0)
			{
			?>
			<li>Ingen grupper!</li>
			<?php
			} else {
				foreach ($user['groups'] as $group)
				{
					?>
			<li><?php echo $group[1]; ?></li>
					<?php
				}
			}
			?>
		</ul>
	</li>
</ul>