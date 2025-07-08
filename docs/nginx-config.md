# Nginx Configuration for GuepardoSys

## Basic Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    # Document root points to public directory
    root /path/to/your/guepardosys/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock; # Adjust PHP version as needed
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_param PHP_VALUE "open_basedir=$document_root:/tmp/:/var/tmp/";
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(env|git|svn|htaccess|htpasswd)$ {
        deny all;
    }
    
    # Deny access to certain directories
    location ~* ^/(vendor|storage|bootstrap|config|database|routes|src)/ {
        deny all;
    }
    
    # Static asset handling
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Additional security
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }
    
    location = /robots.txt {
        log_not_found off;
        access_log off;
    }
}
```

## SSL Configuration (Optional)

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    
    # SSL security settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Rest of the configuration same as HTTP version
    # ... (copy from above)
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## Performance Optimizations

### For High Traffic Sites

```nginx
# Add these directives to your server block for better performance

# FastCGI cache
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=guepardosys:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

location ~ \.php$ {
    # ... existing PHP configuration ...
    
    # Cache configuration
    fastcgi_cache guepardosys;
    fastcgi_cache_valid 200 60m;
    fastcgi_cache_valid 404 10m;
    fastcgi_cache_methods GET HEAD;
    fastcgi_cache_bypass $skip_cache;
    fastcgi_no_cache $skip_cache;
    
    add_header X-Cache-Status $upstream_cache_status;
}

# Skip cache for certain conditions
set $skip_cache 0;

# Skip cache for POST requests
if ($request_method = POST) {
    set $skip_cache 1;
}

# Skip cache for URLs containing query strings
if ($query_string != "") {
    set $skip_cache 1;
}
```

## Notes

1. Adjust the `fastcgi_pass` directive to match your PHP-FPM configuration
2. Replace `/path/to/your/guepardosys/public` with the actual path to your application
3. Replace `your-domain.com` with your actual domain name
4. For shared hosting, these configurations may need to be adapted based on your hosting provider's setup
5. The cache configuration is optional and should be used only if you need high performance
