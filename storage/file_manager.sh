#!/bin/bash

# Root of your project
ROOT_DIR=$(pwd)

# Create main folders
mkdir -p public/css public/js public/images public/uploads
mkdir -p app/controllers/admin app/controllers/ratings app/controllers
mkdir -p app/models
mkdir -p app/views/auth app/views/jobs app/views/profile app/views/admin app/views/shared
mkdir -p api/jobs api/users api/messages api/ratings
mkdir -p config storage/logs storage/cache storage/reports

echo "Folders created."

# Move frontend files
mv "$ROOT_DIR"/index.html public/
mv "$ROOT_DIR"/profile.html public/
mv "$ROOT_DIR"/signup.html public/
mv "$ROOT_DIR"/admin_login.html public/
mv "$ROOT_DIR"/Dashboard.html public/
mv "$ROOT_DIR"/style*.css public/css/
mv "$ROOT_DIR"/*.jpg public/images/
mv "$ROOT_DIR"/*.png public/images/

# Move controller scripts
mv "$ROOT_DIR"/{add_meal.php,apply_job.php,post_job.php,finish_job.php,hire_workers.php,check_worker_hired.php,start_chat.php,send_message.php,update_worker_rating_single.php,update_worker_ratings.php,update_worker_profile.php,update_profile.php} app/controllers/

# Move admin scripts
mv "$ROOT_DIR"/Admins/*.php app/controllers/admin/
mv "$ROOT_DIR"/Admins/*.png public/images/

# Move ratings scripts
mv "$ROOT_DIR"/Ratings/*.php app/controllers/ratings/
mv "$ROOT_DIR"/Ratings/*.png public/images/

# Move models / DB scripts
mv "$ROOT_DIR"/{db.php,db_connection.php,db_config.php} app/models/

# Move API scripts (adjust as needed)
mv "$ROOT_DIR"/{get_jobs.php,hire_workers.php,post_job.php,get_worker_profile.php,get_worker_reviews.php,get_messages.php,process_login.php,register_worker.php,send_message.php,add_worker_rating.php} api/

# Move misc files
mv "$ROOT_DIR"/README.md "$ROOT_DIR"/DO_NOT_UPLOAD_HERE storage/

echo "Files moved. Structure updated."

