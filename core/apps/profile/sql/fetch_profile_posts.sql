SELECT posts.`id` as offset_id, posts.`publication_id`, posts.`type`, posts.`user_id` FROM `<?php echo($data['t_posts']); ?>` posts
	
	INNER JOIN `<?php echo($data['t_pubs']); ?>` pubs ON posts.`publication_id` = pubs.`id`

	WHERE posts.`user_id` = <?php echo($data['user_id']); ?>

	AND pubs.`profile_pinned` = "N"

	<?php if($data['media']): ?>
		AND pubs.`type` IN ('video','image','gif', 'audio')
	<?php endif; ?>

	<?php if($data['offset']): ?>
		AND posts.`id` < <?php echo($data['offset']); ?>
	<?php endif; ?>
	
	ORDER BY posts.`id` DESC, pubs.`likes_count` DESC, pubs.`replys_count` DESC, pubs.`reposts_count` DESC 

<?php if($data['limit']): ?>
	LIMIT <?php echo($data['limit']); ?>;
<?php endif; ?>