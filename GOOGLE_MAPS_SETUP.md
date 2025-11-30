# Google Maps API Setup Guide

## Quick Setup Steps

### 1. Get Your Google Maps API Key

1. **Visit Google Cloud Console**
   - Go to: https://console.cloud.google.com/

2. **Create or Select a Project**
   - Click on the project dropdown at the top
   - Create a new project or select an existing one

3. **Enable Geocoding API**
   - Go to "APIs & Services" → "Library"
   - Search for "Geocoding API"
   - Click on it and press "Enable"

4. **Create API Key**
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "API Key"
   - Copy the generated API key

5. **Restrict Your API Key (Recommended)**
   - Click on the API key you just created
   - Under "API restrictions":
     - Select "Restrict key"
     - Check only "Geocoding API"
   - Under "Application restrictions":
     - Select "HTTP referrers (web sites)"
     - Add your domain (e.g., `localhost/*` for local development)
   - Click "Save"

### 2. Add API Key to Your Project

1. Open `settings/db_cred.php`
2. Find this line:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');
   ```
3. Replace `YOUR_GOOGLE_MAPS_API_KEY_HERE` with your actual API key:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
   ```

### 3. Test the Setup

1. Go to the venue creation or editing page
2. Try searching for an address (e.g., "Treehouse Restaurant, Accra")
3. The search should now work with Google Maps!

## Pricing Information

- **Free Tier**: $200/month credit (usually covers ~40,000 geocoding requests)
- **After Free Tier**: $5 per 1,000 requests
- For most small to medium applications, the free tier is sufficient

## Troubleshooting

### "API key not configured" error
- Make sure you've added your API key to `settings/db_cred.php`
- Check that the key is correct (no extra spaces)

### "REQUEST_DENIED" error
- Make sure the Geocoding API is enabled in your Google Cloud Console
- Check that your API key restrictions allow requests from your domain

### "OVER_QUERY_LIMIT" error
- You've exceeded your quota
- Check your usage in Google Cloud Console
- Consider upgrading your plan or implementing request caching

### Still not finding addresses?
- Try more specific searches (include city name, e.g., "Treehouse Restaurant, Osu, Accra")
- You can always click directly on the map to set the location manually

## Need Help?

- Google Maps API Documentation: https://developers.google.com/maps/documentation/geocoding
- Google Cloud Console: https://console.cloud.google.com/

