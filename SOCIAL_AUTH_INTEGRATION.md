# Social Authentication Integration Guide

## Overview
Complete guide for integrating Facebook and Google social authentication with your streaming platform API.

## API Endpoint

### Social Login/Register
```http
POST /api/auth/login/social
Content-Type: application/json

{
  "provider": "facebook",
  "provider_id": "1234567890",
  "email": "user@example.com",
  "name": "John Doe",
  "avatar_url": "https://graph.facebook.com/1234567890/picture",
  "device_id": "device-uuid-123",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "os_name": "iOS",
  "os_version": "17.0",
  "app_version": "1.0.0"
}
```

**Supported Providers:**
- `facebook`
- `google`
- `apple` (for future implementation)

**Response:**
```json
{
  "message": "Social login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "avatar_url": "https://graph.facebook.com/1234567890/picture",
      "onboarding_completed": false
    },
    "account": {
      "id": "uuid-here",
      "status": "active",
      "profiles": [...]
    },
    "device": {
      "uuid": "device-uuid",
      "device_name": "iPhone 14 Pro",
      "device_type": "mobile",
      "is_active": true
    },
    "token": "1|abcdef123456..."
  }
}
```

## Frontend Integration

### 1. Facebook Login Integration

#### Install Facebook SDK

**For Web (React/Next.js):**
```bash
npm install react-facebook-login
```

**For React Native:**
```bash
npm install react-native-fbsdk-next
npx pod-install  # iOS only
```

#### Web Implementation (React/Next.js)

```tsx
import FacebookLogin from 'react-facebook-login';
import axios from 'axios';

function FacebookLoginButton() {
  const responseFacebook = async (response: any) => {
    if (response.accessToken) {
      try {
        // Call your backend API
        const result = await axios.post('/api/auth/login/social', {
          provider: 'facebook',
          provider_id: response.userID,
          email: response.email,
          name: response.name,
          avatar_url: response.picture?.data?.url,
          // Add device info
          device_id: getDeviceId(), // Your device ID logic
          device_name: getBrowserInfo(),
          device_type: 'desktop',
          os_name: getOSName(),
          browser_name: getBrowserName(),
        });

        // Save token
        localStorage.setItem('token', result.data.data.token);
        
        // Redirect to dashboard or onboarding
        if (!result.data.data.user.onboarding_completed) {
          router.push('/onboarding');
        } else {
          router.push('/dashboard');
        }
      } catch (error) {
        console.error('Social login failed:', error);
      }
    }
  };

  return (
    <FacebookLogin
      appId="YOUR_FACEBOOK_APP_ID"
      autoLoad={false}
      fields="name,email,picture"
      callback={responseFacebook}
      icon="fa-facebook"
    />
  );
}
```

#### React Native Implementation

```tsx
import { LoginManager, AccessToken, Profile } from 'react-native-fbsdk-next';
import axios from 'axios';

async function handleFacebookLogin() {
  try {
    // Request Facebook login
    const result = await LoginManager.logInWithPermissions(['public_profile', 'email']);
    
    if (result.isCancelled) {
      console.log('Login cancelled');
      return;
    }

    // Get access token
    const accessTokenData = await AccessToken.getCurrentAccessToken();
    
    if (!accessTokenData) {
      throw new Error('Failed to get access token');
    }

    // Get user profile
    const userProfile = await Profile.getCurrentProfile();
    
    if (!userProfile) {
      throw new Error('Failed to get user profile');
    }

    // Get user email (need to make Graph API request)
    const response = await fetch(
      `https://graph.facebook.com/me?fields=email&access_token=${accessTokenData.accessToken}`
    );
    const userData = await response.json();

    // Call your backend API
    const result = await axios.post('https://your-api.com/api/auth/login/social', {
      provider: 'facebook',
      provider_id: userProfile.userID,
      email: userData.email,
      name: userProfile.name,
      avatar_url: userProfile.imageURL,
      // Add device info
      device_id: await getDeviceId(),
      device_name: await getDeviceName(),
      device_type: 'mobile',
      os_name: Platform.OS,
      os_version: Platform.Version.toString(),
      app_version: '1.0.0',
    });

    // Save token and navigate
    await AsyncStorage.setItem('token', result.data.data.token);
    
    if (!result.data.data.user.onboarding_completed) {
      navigation.navigate('Onboarding');
    } else {
      navigation.navigate('Home');
    }
  } catch (error) {
    console.error('Facebook login failed:', error);
  }
}
```

### 2. Google Login Integration

#### Install Google SDK

**For Web (React/Next.js):**
```bash
npm install @react-oauth/google
```

**For React Native:**
```bash
npm install @react-native-google-signin/google-signin
npx pod-install  # iOS only
```

#### Web Implementation (React/Next.js)

```tsx
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';
import { jwtDecode } from 'jwt-decode';
import axios from 'axios';

function App() {
  return (
    <GoogleOAuthProvider clientId="YOUR_GOOGLE_CLIENT_ID">
      <GoogleLoginButton />
    </GoogleOAuthProvider>
  );
}

function GoogleLoginButton() {
  const handleGoogleSuccess = async (credentialResponse: any) => {
    try {
      // Decode Google JWT token
      const decoded: any = jwtDecode(credentialResponse.credential);

      // Call your backend API
      const result = await axios.post('/api/auth/login/social', {
        provider: 'google',
        provider_id: decoded.sub,
        email: decoded.email,
        name: decoded.name,
        avatar_url: decoded.picture,
        // Add device info
        device_id: getDeviceId(),
        device_name: getBrowserInfo(),
        device_type: 'desktop',
        os_name: getOSName(),
        browser_name: getBrowserName(),
      });

      // Save token
      localStorage.setItem('token', result.data.data.token);
      
      // Redirect
      if (!result.data.data.user.onboarding_completed) {
        router.push('/onboarding');
      } else {
        router.push('/dashboard');
      }
    } catch (error) {
      console.error('Google login failed:', error);
    }
  };

  return (
    <GoogleLogin
      onSuccess={handleGoogleSuccess}
      onError={() => console.log('Login Failed')}
    />
  );
}
```

#### React Native Implementation

```tsx
import { GoogleSignin, statusCodes } from '@react-native-google-signin/google-signin';
import axios from 'axios';

// Configure Google Sign-In
GoogleSignin.configure({
  webClientId: 'YOUR_WEB_CLIENT_ID', // From Google Cloud Console
  iosClientId: 'YOUR_IOS_CLIENT_ID', // From Google Cloud Console
});

async function handleGoogleLogin() {
  try {
    // Check if device supports Google Play Services
    await GoogleSignin.hasPlayServices();

    // Sign in
    const userInfo = await GoogleSignin.signIn();

    // Call your backend API
    const result = await axios.post('https://your-api.com/api/auth/login/social', {
      provider: 'google',
      provider_id: userInfo.user.id,
      email: userInfo.user.email,
      name: userInfo.user.name,
      avatar_url: userInfo.user.photo,
      // Add device info
      device_id: await getDeviceId(),
      device_name: await getDeviceName(),
      device_type: 'mobile',
      os_name: Platform.OS,
      os_version: Platform.Version.toString(),
      app_version: '1.0.0',
    });

    // Save token and navigate
    await AsyncStorage.setItem('token', result.data.data.token);
    
    if (!result.data.data.user.onboarding_completed) {
      navigation.navigate('Onboarding');
    } else {
      navigation.navigate('Home');
    }
  } catch (error: any) {
    if (error.code === statusCodes.SIGN_IN_CANCELLED) {
      console.log('User cancelled the login');
    } else if (error.code === statusCodes.IN_PROGRESS) {
      console.log('Sign in is in progress');
    } else if (error.code === statusCodes.PLAY_SERVICES_NOT_AVAILABLE) {
      console.log('Play services not available');
    } else {
      console.error('Google login failed:', error);
    }
  }
}
```

## Backend Configuration

### Facebook App Setup

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or use existing
3. Add "Facebook Login" product
4. Configure OAuth redirect URIs:
   - Web: `https://your-domain.com/auth/facebook/callback`
   - Mobile: Add your app's bundle ID/package name
5. Get your **App ID** and **App Secret**
6. Add these to your `.env` (optional, only needed if using Socialite):
   ```env
   FACEBOOK_CLIENT_ID=your_app_id
   FACEBOOK_CLIENT_SECRET=your_app_secret
   FACEBOOK_REDIRECT_URI=https://your-domain.com/auth/facebook/callback
   ```

### Google Cloud Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable "Google+ API"
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure OAuth consent screen
6. Create credentials for:
   - **Web application** (for web frontend)
   - **iOS** (for iOS app)
   - **Android** (for Android app)
7. Add authorized redirect URIs
8. Get your **Client ID** and **Client Secret**
9. Add these to your `.env` (optional, only needed if using Socialite):
   ```env
   GOOGLE_CLIENT_ID=your_client_id
   GOOGLE_CLIENT_SECRET=your_client_secret
   GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback
   ```

## Device ID Generation

### Web Implementation

```typescript
// utils/device.ts
export function getDeviceId(): string {
  let deviceId = localStorage.getItem('device_id');
  
  if (!deviceId) {
    deviceId = `web-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    localStorage.setItem('device_id', deviceId);
  }
  
  return deviceId;
}

export function getBrowserInfo(): string {
  const ua = navigator.userAgent;
  let browserName = 'Unknown';
  
  if (ua.indexOf('Firefox') > -1) browserName = 'Firefox';
  else if (ua.indexOf('Chrome') > -1) browserName = 'Chrome';
  else if (ua.indexOf('Safari') > -1) browserName = 'Safari';
  else if (ua.indexOf('Edge') > -1) browserName = 'Edge';
  
  return browserName;
}

export function getOSName(): string {
  const ua = navigator.userAgent;
  
  if (ua.indexOf('Win') > -1) return 'Windows';
  if (ua.indexOf('Mac') > -1) return 'macOS';
  if (ua.indexOf('Linux') > -1) return 'Linux';
  if (ua.indexOf('Android') > -1) return 'Android';
  if (ua.indexOf('iOS') > -1) return 'iOS';
  
  return 'Unknown';
}
```

### React Native Implementation

```typescript
import DeviceInfo from 'react-native-device-info';
import AsyncStorage from '@react-native-async-storage/async-storage';

export async function getDeviceId(): Promise<string> {
  let deviceId = await AsyncStorage.getItem('device_id');
  
  if (!deviceId) {
    // Use unique device ID
    deviceId = await DeviceInfo.getUniqueId();
    await AsyncStorage.setItem('device_id', deviceId);
  }
  
  return deviceId;
}

export async function getDeviceName(): Promise<string> {
  return await DeviceInfo.getDeviceName();
}
```

## Complete Flow Example

### 1. User clicks "Login with Facebook"
### 2. Frontend initiates Facebook OAuth
### 3. User authorizes app on Facebook
### 4. Frontend receives user data from Facebook
### 5. Frontend calls `/api/auth/login/social` with:
   - Facebook user data
   - Device information
### 6. Backend checks if user exists:
   - **Exists**: Login user, register device, return token
   - **New user**: Create user, create account, create profile, subscribe to free package, register device, return token
### 7. Frontend receives token and user data
### 8. Frontend saves token and checks onboarding status:
   - **Not completed**: Redirect to onboarding
   - **Completed**: Redirect to dashboard

## Testing with Postman/Insomnia

```bash
POST http://localhost/api/auth/login/social
Content-Type: application/json

{
  "provider": "facebook",
  "provider_id": "test-fb-id-123",
  "email": "test@example.com",
  "name": "Test User",
  "avatar_url": "https://example.com/avatar.jpg",
  "device_id": "test-device-123",
  "device_name": "Test Device",
  "device_type": "mobile",
  "os_name": "iOS",
  "os_version": "17.0"
}
```

## Security Best Practices

1. **Always validate tokens server-side** (optional but recommended):
   ```php
   // Verify Facebook token
   $response = Http::get("https://graph.facebook.com/me?access_token={$fbAccessToken}");
   
   // Verify Google token
   $response = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$googleIdToken}");
   ```

2. **Use HTTPS** for all API calls
3. **Implement rate limiting** on social login endpoints
4. **Store tokens securely** (encrypted storage on mobile, HttpOnly cookies on web)
5. **Validate email domains** if needed
6. **Implement device limit** (already done - max 10 devices)

## Troubleshooting

### Facebook Issues
- **Email not returned**: Request `email` permission explicitly
- **Invalid OAuth redirect URI**: Check Facebook App settings
- **App not live**: Facebook apps must be reviewed for public use

### Google Issues
- **Invalid Client ID**: Ensure you're using the correct client ID for the platform
- **Sign-in popup blocked**: Configure popup settings
- **Token expired**: Implement token refresh logic

## Next Steps

After successful social login:
1. Complete onboarding (if not done)
2. Select interests
3. Browse content
4. Start streaming!
