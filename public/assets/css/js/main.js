$(function() {
  console.log('jQuery loaded successfully');
  console.log('Document ready');
  
  // Add post
  $('#addPostForm').on('submit', function(e) {
    e.preventDefault();
    var form = new FormData(this);
    form.append('action', 'add_post');
    $.ajax({
      url: 'ajax.php',
      method: 'POST',
      data: form,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(resp) {
        if (resp.success) {
          location.reload();
        } else {
          alert('Error adding post: ' + (resp.msg || ''));
        }
      }
    });
  });

  // Delete post
  $(document).on('click', '.delete-post', function() {
    if (!confirm('Delete this post?')) return;
    var id = $(this).data('id');
    $.post('ajax.php', { action: 'delete_post', post_id: id }, function(resp) {
      if (resp.success) $('#post-' + id).remove();
      else alert('Failed to delete');
    }, 'json');
  });

  // Like / Dislike
  $(document).on('click', '.react-btn', function() {
    console.log('React button click event triggered');
    var $btn = $(this);
    var id = $btn.data('id');
    var value = $btn.data('val'); // 1 = like, -1 = dislike
    var $post = $btn.closest('.card');
    
    console.log('React button clicked:', { id: id, value: value });
    
    // Check if this button is already active (user wants to remove reaction)
    var isCurrentlyActive = $btn.hasClass('active');
    var newValue = isCurrentlyActive ? 0 : value; // 0 means remove reaction
    
    console.log('Sending reaction:', { post_id: id, value: newValue });
    
    $.post('ajax.php', { action: 'react', post_id: id, value: newValue }, function(resp) {
      console.log('Reaction response:', resp);
      if (resp.success) {
        // Update button states
        if (newValue === 0) {
          // Remove all active states
          $post.find('.react-btn').removeClass('active');
        } else {
          // Set only the clicked button as active
          $post.find('.react-btn').removeClass('active');
          $btn.addClass('active');
        }
        
        // Update counts dynamically
        updatePostCounts(id);
      } else {
        alert('Failed to react: ' + (resp.msg || 'Unknown error'));
      }
    }, 'json').fail(function(xhr, status, error) {
      console.error('AJAX error:', { xhr: xhr, status: status, error: error });
      alert('AJAX request failed: ' + error);
    });
  });

  // Function to update post counts without page reload
  function updatePostCounts(postId) {
    console.log('Updating counts for post:', postId);
    $.post('ajax.php', { action: 'get_post_counts', post_id: postId }, function(resp) {
      console.log('Counts response:', resp);
      if (resp.success) {
        var $post = $('#post-' + postId);
        console.log('Updating counts:', { likes: resp.likes, dislikes: resp.dislikes, comments: resp.comments });
        $post.find('.btn-like span').text(resp.likes);
        $post.find('.btn-dislike span').text(resp.dislikes);
        $post.find('.btn-comment span').text(resp.comments);
      }
    }, 'json').fail(function(xhr, status, error) {
      console.error('Counts AJAX error:', { xhr: xhr, status: status, error: error });
    });
  }

  // Update profile
  $('#updateProfileForm').on('submit', function(e) {
    e.preventDefault();
    var form = new FormData(this);
    form.append('action', 'update_profile');
    $.ajax({
      url: 'ajax.php',
      method: 'POST',
      data: form,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(resp) {
        if (resp.success) {
          alert('Profile updated');
          location.reload();
        } else {
          alert('Failed to update profile');
        }
      }
    });
  });

  // Toggle comments section
  $(document).on('click', '.comment-toggle', function() {
    var postId = $(this).data('id');
    var commentsSection = $('#comments-' + postId);
    var commentsList = $('#comments-list-' + postId);
    
    console.log('Comment toggle clicked for post:', postId);
    console.log('Comments section:', commentsSection);
    console.log('Has show class:', commentsSection.hasClass('show'));
    
    if (commentsSection.hasClass('show')) {
      commentsSection.removeClass('show');
      $(this).removeClass('active');
      console.log('Hiding comments section');
    } else {
      commentsSection.addClass('show');
      $(this).addClass('active');
      console.log('Showing comments section');
      
      // Load comments if not already loaded
      if (commentsList.children().length === 0) {
        loadComments(postId);
      }
    }
  });

  // Add comment
  $(document).on('click', '.comment-submit', function() {
    var postId = $(this).data('id');
    var content = $(this).siblings('.comment-text').val().trim();
    
    if (!content) {
      alert('Please enter a comment');
      return;
    }
    
    $.post('ajax.php', {
      action: 'add_comment',
      post_id: postId,
      content: content
    }, function(resp) {
      if (resp.success) {
        // Clear the textarea
        $('.comment-text').val('');
        
        // Add the new comment to the list
        addCommentToList(postId, resp.comment);
        
        // Update comment count
        var commentBtn = $('.comment-toggle[data-id="' + postId + '"]');
        var currentCount = parseInt(commentBtn.find('span').text()) || 0;
        commentBtn.find('span').text(currentCount + 1);
        
        // Also update the comment count in the post actions
        var $post = $('#post-' + postId);
        $post.find('.btn-comment span').text(currentCount + 1);
      } else {
        alert('Failed to add comment: ' + (resp.msg || ''));
      }
    }, 'json');
  });

  // Load comments for a post
  function loadComments(postId) {
    $.post('ajax.php', {
      action: 'get_comments',
      post_id: postId
    }, function(resp) {
      if (resp.success) {
        var commentsList = $('#comments-list-' + postId);
        commentsList.empty();
        
        if (resp.comments.length === 0) {
          commentsList.html('<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>');
        } else {
          resp.comments.forEach(function(comment) {
            addCommentToList(postId, comment);
          });
        }
      }
    }, 'json');
  }

  // Add a comment to the comments list
  function addCommentToList(postId, comment) {
    var commentsList = $('#comments-list-' + postId);
    var commentHtml = createCommentHtml(comment);
    commentsList.append(commentHtml);
    
    // Debug: Check the actual rendered HTML
    console.log('Added comment HTML:', commentHtml);
    
    // Debug: Check if the avatar has the right size
    setTimeout(function() {
      var avatar = commentsList.find('.comment-avatar').last();
      console.log('Avatar element:', avatar);
      console.log('Avatar computed style:', {
        width: avatar.css('width'),
        height: avatar.css('height'),
        maxWidth: avatar.css('max-width'),
        maxHeight: avatar.css('max-height')
      });
    }, 100);
  }

  // Create HTML for a single comment
  function createCommentHtml(comment) {
    var timeAgo = getTimeAgo(comment.created_at);
    var profilePic = comment.profile_pic || '';
    
    console.log('Creating comment HTML:', { 
      id: comment.id, 
      name: comment.full_name, 
      profilePic: profilePic 
    });
    
    // Create a simple avatar if no profile picture
    var avatarHtml = profilePic ? 
      `<img src="${profilePic}" class="comment-avatar" alt="${comment.full_name}">` :
      `<div class="comment-avatar comment-avatar-default">${comment.full_name.charAt(0).toUpperCase()}</div>`;
    
    return `
      <div class="comment-item" data-comment-id="${comment.id}">
        <div class="comment-header">
          ${avatarHtml}
          <span class="comment-author">${comment.full_name}</span>
          <span class="comment-time">${timeAgo}</span>
        </div>
        <p class="comment-content">${comment.content}</p>
      </div>
    `;
  }

  // Simple time ago function
  function getTimeAgo(dateString) {
    var now = new Date();
    var commentDate = new Date(dateString);
    var diffInSeconds = Math.floor((now - commentDate) / 1000);
    
    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
    return Math.floor(diffInSeconds / 86400) + 'd ago';
  }

  // File upload functionality
  function setupFileUpload(inputId, nameId) {
    var fileInput = document.getElementById(inputId);
    var fileNameDiv = document.getElementById(nameId);
    
    if (fileInput && fileNameDiv) {
      fileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
          fileNameDiv.textContent = 'Selected: ' + file.name;
          fileNameDiv.style.display = 'block';
        } else {
          fileNameDiv.textContent = '';
          fileNameDiv.style.display = 'none';
        }
      });
    }
  }

  // Initialize file uploads when page loads
  setupFileUpload('profile-pic-upload', 'profile-pic-name');
  setupFileUpload('post-image-upload', 'post-image-name');
  setupFileUpload('signup-profile-upload', 'signup-profile-name');
});
