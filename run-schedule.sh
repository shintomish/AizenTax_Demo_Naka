#!/bin/bash
cd /home/shintomi/act100 || exit 1

# 実行
docker compose run --rm laravel.test php artisan schedule:run \
    >> storage/logs/scheduler-$(date +\%Y-\%m-\%d).log 2>&1

# 30日より古いログを削除
find storage/logs/ -name 'scheduler-*.log' -mtime +30 -delete
