# Complete User Onboarding Flow

Full end-to-end guide for user registration, profile setup, subscription selection, and profile management.

---

## Table of Contents

1. [Overview](#overview)
2. [Step 1: User Registration](#step-1-user-registration)
3. [Step 2: Email Verification](#step-2-email-verification)
4. [Step 3: Subscription Selection](#step-3-subscription-selection)
5. [Step 4: Payment Setup](#step-4-payment-setup)
6. [Step 5: Create First Profile](#step-5-create-first-profile)
7. [Step 6: Profile Management](#step-6-profile-management)
8. [Step 7: Start Watching](#step-7-start-watching)
9. [Complete Implementation Examples](#complete-implementation-examples)

---

## Overview

### Onboarding Journey

```
Registration → Email Verification → Choose Plan → Payment → Create Profile → Start Watching
```

### What Users Get After Onboarding:

- ✅ Verified account with authentication token
- ✅ Active subscription (or trial)
- ✅ At least one profile created
- ✅ Access to content based on subscription tier
- ✅ Ability to create multiple profiles (up to plan limit)

---

## Step 1: User Registration

### Endpoint
```
POST /api/register
```

### Request Body

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "accept_terms": true,
  "marketing_consent": false
}
```

### Field Validation

| Field | Required | Rules |
|-------|----------|-------|
| name | Yes | Min: 2 chars, Max: 255 chars |
| email | Yes | Valid email, unique |
| password | Yes | Min: 8 chars, must include uppercase, lowercase, number |
| password_confirmation | Yes | Must match password |
| accept_terms | Yes | Must be true |
| marketing_consent | No | Boolean (optional) |

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "email_verified_at": null,
      "created_at": "2025-11-17T10:00:00.000000Z"
    },
    "token": "1|abcdef123456789tokenstring",
    "verification_sent": true
  }
}
```

### Error Responses

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Frontend Implementation

```javascript
// Registration Form Component (React)
import { useState } from 'react';

function RegistrationForm({ onSuccess }) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    accept_terms: false,
    marketing_consent: false
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    try {
      const response = await fetch('https://your-domain.com/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (data.success) {
        // Store auth token
        localStorage.setItem('token', data.data.token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
        
        // Proceed to next step
        onSuccess(data.data);
      } else {
        setErrors(data.errors || {});
      }
    } catch (error) {
      setErrors({ general: 'Registration failed. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="registration-form">
      <h2>Create Your Account</h2>
      
      <div className="form-group">
        <label htmlFor="name">Full Name</label>
        <input
          type="text"
          id="name"
          value={formData.name}
          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          placeholder="Enter your full name"
          required
        />
        {errors.name && <span className="error">{errors.name[0]}</span>}
      </div>

      <div className="form-group">
        <label htmlFor="email">Email Address</label>
        <input
          type="email"
          id="email"
          value={formData.email}
          onChange={(e) => setFormData({ ...formData, email: e.target.value })}
          placeholder="you@example.com"
          required
        />
        {errors.email && <span className="error">{errors.email[0]}</span>}
      </div>

      <div className="form-group">
        <label htmlFor="password">Password</label>
        <input
          type="password"
          id="password"
          value={formData.password}
          onChange={(e) => setFormData({ ...formData, password: e.target.value })}
          placeholder="Min. 8 characters"
          required
        />
        {errors.password && <span className="error">{errors.password[0]}</span>}
        <small>Must include uppercase, lowercase, and number</small>
      </div>

      <div className="form-group">
        <label htmlFor="password_confirmation">Confirm Password</label>
        <input
          type="password"
          id="password_confirmation"
          value={formData.password_confirmation}
          onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
          placeholder="Re-enter password"
          required
        />
      </div>

      <div className="form-group checkbox">
        <label>
          <input
            type="checkbox"
            checked={formData.accept_terms}
            onChange={(e) => setFormData({ ...formData, accept_terms: e.target.checked })}
            required
          />
          I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a>
        </label>
        {errors.accept_terms && <span className="error">{errors.accept_terms[0]}</span>}
      </div>

      <div className="form-group checkbox">
        <label>
          <input
            type="checkbox"
            checked={formData.marketing_consent}
            onChange={(e) => setFormData({ ...formData, marketing_consent: e.target.checked })}
          />
          Send me updates and special offers
        </label>
      </div>

      {errors.general && <div className="error-banner">{errors.general}</div>}

      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? 'Creating Account...' : 'Create Account'}
      </button>

      <p className="login-link">
        Already have an account? <a href="/login">Sign In</a>
      </p>
    </form>
  );
}
```

---

## Step 2: Email Verification

After registration, users receive a verification email.

### Check Verification Status

```
GET /api/user
```

**Headers:**
```
Authorization: Bearer TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": null,
    "is_email_verified": false
  }
}
```

### Resend Verification Email

```
POST /api/email/verification-notification
```

**Headers:**
```
Authorization: Bearer TOKEN
```

**Response:**
```json
{
  "success": true,
  "message": "Verification email sent"
}
```

### Verify Email (User clicks link in email)

```
GET /api/email/verify/{id}/{hash}
```

**Response:**
```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

### Frontend Implementation

```javascript
function EmailVerificationPrompt({ user, onVerified }) {
  const [sending, setSending] = useState(false);
  const [message, setMessage] = useState('');

  const resendVerification = async () => {
    setSending(true);
    const token = localStorage.getItem('token');

    try {
      const response = await fetch('https://your-domain.com/api/email/verification-notification', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();
      setMessage(data.message);
    } catch (error) {
      setMessage('Failed to send verification email');
    } finally {
      setSending(false);
    }
  };

  return (
    <div className="verification-prompt">
      <div className="icon">📧</div>
      <h2>Verify Your Email</h2>
      <p>We've sent a verification link to <strong>{user.email}</strong></p>
      <p>Please check your inbox and click the link to verify your account.</p>
      
      {message && <div className="message">{message}</div>}
      
      <button onClick={resendVerification} disabled={sending}>
        {sending ? 'Sending...' : 'Resend Verification Email'}
      </button>
      
      <button onClick={onVerified} className="btn-link">
        I've verified my email
      </button>
    </div>
  );
}
```

---

## Step 3: Subscription Selection

### Get Available Plans

```
GET /api/subscription-plans
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Basic",
      "slug": "basic",
      "description": "Perfect for casual viewers",
      "price": 9.99,
      "currency": "USD",
      "interval": "monthly",
      "trial_days": 7,
      "features": [
        "HD streaming",
        "Watch on 1 device",
        "Limited catalog access"
      ],
      "max_concurrent_streams": 1,
      "max_profiles": 2,
      "video_quality": "hd",
      "has_ads": true
    },
    {
      "id": 2,
      "name": "Standard",
      "slug": "standard",
      "description": "Most popular plan",
      "price": 14.99,
      "currency": "USD",
      "interval": "monthly",
      "trial_days": 14,
      "features": [
        "Full HD streaming",
        "Watch on 2 devices",
        "Full catalog access",
        "Ad-free experience",
        "Download for offline"
      ],
      "max_concurrent_streams": 2,
      "max_profiles": 5,
      "video_quality": "full_hd",
      "has_ads": false,
      "is_popular": true
    },
    {
      "id": 3,
      "name": "Premium",
      "slug": "premium",
      "description": "Ultimate viewing experience",
      "price": 19.99,
      "currency": "USD",
      "interval": "monthly",
      "trial_days": 14,
      "features": [
        "4K Ultra HD streaming",
        "Watch on 4 devices",
        "Full catalog access",
        "Ad-free experience",
        "Download for offline",
        "Early access to new releases"
      ],
      "max_concurrent_streams": 4,
      "max_profiles": 10,
      "video_quality": "ultra_hd",
      "has_ads": false
    }
  ]
}
```

### Frontend Implementation

```javascript
function SubscriptionPlans({ onSelectPlan }) {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedPlan, setSelectedPlan] = useState(null);

  useEffect(() => {
    loadPlans();
  }, []);

  const loadPlans = async () => {
    try {
      const response = await fetch('https://your-domain.com/api/subscription-plans');
      const data = await response.json();
      
      if (data.success) {
        setPlans(data.data);
        // Pre-select the popular plan
        const popularPlan = data.data.find(p => p.is_popular);
        if (popularPlan) setSelectedPlan(popularPlan.id);
      }
    } catch (error) {
      console.error('Failed to load plans:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleContinue = () => {
    const plan = plans.find(p => p.id === selectedPlan);
    if (plan) {
      onSelectPlan(plan);
    }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="subscription-plans">
      <h2>Choose Your Plan</h2>
      <p className="subtitle">Start with a free trial. Cancel anytime.</p>

      <div className="plans-grid">
        {plans.map(plan => (
          <div
            key={plan.id}
            className={`plan-card ${selectedPlan === plan.id ? 'selected' : ''} ${plan.is_popular ? 'popular' : ''}`}
            onClick={() => setSelectedPlan(plan.id)}
          >
            {plan.is_popular && <div className="badge">Most Popular</div>}
            
            <h3>{plan.name}</h3>
            <p className="description">{plan.description}</p>
            
            <div className="price">
              <span className="amount">${plan.price}</span>
              <span className="interval">/{plan.interval}</span>
            </div>

            {plan.trial_days > 0 && (
              <div className="trial-info">
                {plan.trial_days} days free trial
              </div>
            )}

            <ul className="features">
              {plan.features.map((feature, index) => (
                <li key={index}>
                  <span className="icon">✓</span>
                  {feature}
                </li>
              ))}
            </ul>

            <div className="plan-details">
              <small>Up to {plan.max_profiles} profiles</small>
              <small>{plan.video_quality.toUpperCase()} quality</small>
            </div>
          </div>
        ))}
      </div>

      <button
        onClick={handleContinue}
        disabled={!selectedPlan}
        className="btn-primary btn-large"
      >
        Continue with {plans.find(p => p.id === selectedPlan)?.name}
      </button>

      <p className="terms-note">
        By continuing, you agree to our Terms of Service. Cancel anytime during trial.
      </p>
    </div>
  );
}
```

---

## Step 4: Payment Setup

### Create Subscription with Payment Method

```
POST /api/subscriptions
```

**Headers:**
```
Authorization: Bearer TOKEN
Content-Type: application/json
```

**Request Body:**
```json
{
  "plan_id": 2,
  "payment_method_id": "pm_1234567890abcdef",
  "use_trial": true
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Subscription created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "plan_id": 2,
    "status": "trialing",
    "trial_ends_at": "2025-12-01T10:00:00.000000Z",
    "current_period_end": "2025-12-15T10:00:00.000000Z",
    "plan": {
      "id": 2,
      "name": "Standard",
      "price": 14.99,
      "interval": "monthly",
      "max_profiles": 5
    }
  }
}
```

### Frontend Implementation with Stripe

```javascript
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe('pk_test_your_publishable_key');

function PaymentSetup({ plan, onSuccess }) {
  return (
    <Elements stripe={stripePromise}>
      <PaymentForm plan={plan} onSuccess={onSuccess} />
    </Elements>
  );
}

function PaymentForm({ plan, onSuccess }) {
  const stripe = useStripe();
  const elements = useElements();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    if (!stripe || !elements) {
      return;
    }

    try {
      // Create payment method with Stripe
      const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
        type: 'card',
        card: elements.getElement(CardElement),
        billing_details: {
          name: user.name,
          email: user.email
        }
      });

      if (stripeError) {
        setError(stripeError.message);
        setLoading(false);
        return;
      }

      // Create subscription with payment method
      const response = await fetch('https://your-domain.com/api/subscriptions', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          plan_id: plan.id,
          payment_method_id: paymentMethod.id,
          use_trial: true
        })
      });

      const data = await response.json();

      if (data.success) {
        // Store subscription info
        localStorage.setItem('subscription', JSON.stringify(data.data));
        onSuccess(data.data);
      } else {
        setError(data.message || 'Subscription creation failed');
      }
    } catch (error) {
      setError('Payment processing failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="payment-setup">
      <h2>Payment Information</h2>
      
      <div className="plan-summary">
        <h3>{plan.name} Plan</h3>
        <p>{plan.description}</p>
        <div className="price-info">
          <strong>${plan.price}/{plan.interval}</strong>
          {plan.trial_days > 0 && (
            <span className="trial-badge">
              {plan.trial_days} days free trial
            </span>
          )}
        </div>
      </div>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Card Information</label>
          <div className="card-element-container">
            <CardElement
              options={{
                style: {
                  base: {
                    fontSize: '16px',
                    color: '#424770',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    '::placeholder': {
                      color: '#aab7c4',
                    },
                  },
                  invalid: {
                    color: '#9e2146',
                  },
                },
              }}
            />
          </div>
        </div>

        {error && <div className="error-message">{error}</div>}

        <div className="trial-notice">
          <p>
            <strong>Your trial starts today.</strong>
            {plan.trial_days > 0 && (
              <>
                <br />
                You won't be charged until {new Date(Date.now() + plan.trial_days * 24 * 60 * 60 * 1000).toLocaleDateString()}.
                Cancel anytime before then at no charge.
              </>
            )}
          </p>
        </div>

        <button
          type="submit"
          disabled={!stripe || loading}
          className="btn-primary btn-large"
        >
          {loading ? 'Processing...' : `Start ${plan.trial_days}-Day Free Trial`}
        </button>

        <p className="secure-notice">
          🔒 Secure payment powered by Stripe
        </p>
      </form>
    </div>
  );
}
```

---

## Step 5: Create First Profile

### Create Profile

```
POST /api/profiles
```

**Headers:**
```
Authorization: Bearer TOKEN
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John",
  "avatar": "avatar1.png",
  "is_kids": false,
  "is_default": true,
  "preferences": {
    "autoplay_next": true,
    "subtitle_preference": "off",
    "quality_preference": "auto"
  }
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Profile created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "John",
    "avatar": "avatar1.png",
    "is_kids": false,
    "is_default": true,
    "preferences": {
      "autoplay_next": true,
      "subtitle_preference": "off",
      "quality_preference": "auto"
    },
    "created_at": "2025-11-17T10:00:00.000000Z"
  }
}
```

### Frontend Implementation

```javascript
function CreateProfileStep({ onSuccess }) {
  const [profileData, setProfileData] = useState({
    name: '',
    avatar: 'avatar1.png',
    is_kids: false,
    is_default: true,
    preferences: {
      autoplay_next: true,
      subtitle_preference: 'off',
      quality_preference: 'auto'
    }
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const token = localStorage.getItem('token');

  const avatars = [
    'avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png',
    'avatar5.png', 'avatar6.png', 'avatar7.png', 'avatar8.png'
  ];

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('https://your-domain.com/api/profiles', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(profileData)
      });

      const data = await response.json();

      if (data.success) {
        // Store profile info
        localStorage.setItem('current_profile', JSON.stringify(data.data));
        onSuccess(data.data);
      } else {
        setError(data.message || 'Profile creation failed');
      }
    } catch (error) {
      setError('Failed to create profile. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="create-profile">
      <h2>Create Your Profile</h2>
      <p className="subtitle">Personalize your viewing experience</p>

      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Profile Name</label>
          <input
            type="text"
            value={profileData.name}
            onChange={(e) => setProfileData({ ...profileData, name: e.target.value })}
            placeholder="Enter profile name"
            maxLength="50"
            required
          />
        </div>

        <div className="form-group">
          <label>Choose Avatar</label>
          <div className="avatar-grid">
            {avatars.map(avatar => (
              <div
                key={avatar}
                className={`avatar-option ${profileData.avatar === avatar ? 'selected' : ''}`}
                onClick={() => setProfileData({ ...profileData, avatar })}
              >
                <img src={`/images/avatars/${avatar}`} alt={avatar} />
              </div>
            ))}
          </div>
        </div>

        <div className="form-group checkbox">
          <label>
            <input
              type="checkbox"
              checked={profileData.is_kids}
              onChange={(e) => setProfileData({ ...profileData, is_kids: e.target.checked })}
            />
            This is a kids profile (only shows age-appropriate content)
          </label>
        </div>

        <div className="form-group">
          <label>Preferences</label>
          
          <div className="checkbox">
            <label>
              <input
                type="checkbox"
                checked={profileData.preferences.autoplay_next}
                onChange={(e) => setProfileData({
                  ...profileData,
                  preferences: { ...profileData.preferences, autoplay_next: e.target.checked }
                })}
              />
              Autoplay next episode
            </label>
          </div>

          <div className="preference-row">
            <label>Subtitle Preference</label>
            <select
              value={profileData.preferences.subtitle_preference}
              onChange={(e) => setProfileData({
                ...profileData,
                preferences: { ...profileData.preferences, subtitle_preference: e.target.value }
              })}
            >
              <option value="off">Off</option>
              <option value="english">English</option>
              <option value="spanish">Spanish</option>
              <option value="french">French</option>
            </select>
          </div>

          <div className="preference-row">
            <label>Video Quality</label>
            <select
              value={profileData.preferences.quality_preference}
              onChange={(e) => setProfileData({
                ...profileData,
                preferences: { ...profileData.preferences, quality_preference: e.target.value }
              })}
            >
              <option value="auto">Auto</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low (save data)</option>
            </select>
          </div>
        </div>

        {error && <div className="error-message">{error}</div>}

        <button type="submit" disabled={loading} className="btn-primary btn-large">
          {loading ? 'Creating Profile...' : 'Create Profile'}
        </button>
      </form>
    </div>
  );
}
```

---

## Step 6: Profile Management

### Get All Profiles

```
GET /api/profiles
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John",
      "avatar": "avatar1.png",
      "is_kids": false,
      "is_default": true,
      "created_at": "2025-11-17T10:00:00.000000Z"
    }
  ]
}
```

### Create Additional Profiles

Users can create multiple profiles up to their plan limit.

```
POST /api/profiles
```

### Update Profile

```
PUT /api/profiles/{id}
```

**Request:**
```json
{
  "name": "John (Updated)",
  "avatar": "avatar5.png",
  "preferences": {
    "autoplay_next": false
  }
}
```

### Delete Profile

```
DELETE /api/profiles/{id}
```

**Note:** Cannot delete the default profile.

### Frontend Implementation

```javascript
function ProfileManagement() {
  const [profiles, setProfiles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const token = localStorage.getItem('token');
  const subscription = JSON.parse(localStorage.getItem('subscription'));

  useEffect(() => {
    loadProfiles();
  }, []);

  const loadProfiles = async () => {
    try {
      const response = await fetch('https://your-domain.com/api/profiles', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();
      if (data.success) {
        setProfiles(data.data);
      }
    } catch (error) {
      console.error('Failed to load profiles:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSelectProfile = (profile) => {
    localStorage.setItem('current_profile', JSON.stringify(profile));
    window.location.href = '/browse';
  };

  const handleDeleteProfile = async (profileId) => {
    if (!confirm('Are you sure you want to delete this profile?')) return;

    try {
      const response = await fetch(`https://your-domain.com/api/profiles/${profileId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();
      if (data.success) {
        loadProfiles();
      }
    } catch (error) {
      alert('Failed to delete profile');
    }
  };

  const canAddMoreProfiles = profiles.length < subscription?.plan?.max_profiles;

  if (loading) return <LoadingSpinner />;

  return (
    <div className="profile-management">
      <h1>Who's Watching?</h1>

      <div className="profiles-grid">
        {profiles.map(profile => (
          <div key={profile.id} className="profile-card">
            <div
              className="profile-avatar"
              onClick={() => handleSelectProfile(profile)}
            >
              <img src={`/images/avatars/${profile.avatar}`} alt={profile.name} />
              {profile.is_kids && <span className="kids-badge">Kids</span>}
            </div>
            <h3>{profile.name}</h3>
            {profile.is_default && <span className="default-badge">Default</span>}
            
            <div className="profile-actions">
              <button onClick={() => handleEditProfile(profile)}>
                Edit
              </button>
              {!profile.is_default && (
                <button
                  onClick={() => handleDeleteProfile(profile.id)}
                  className="btn-danger"
                >
                  Delete
                </button>
              )}
            </div>
          </div>
        ))}

        {canAddMoreProfiles && (
          <div className="profile-card add-profile" onClick={() => setShowCreateModal(true)}>
            <div className="add-icon">+</div>
            <h3>Add Profile</h3>
            <small>{profiles.length} of {subscription?.plan?.max_profiles} profiles</small>
          </div>
        )}
      </div>

      {!canAddMoreProfiles && (
        <p className="limit-notice">
          You've reached the maximum number of profiles for your plan.
          <a href="/subscription">Upgrade to add more</a>
        </p>
      )}

      {showCreateModal && (
        <Modal onClose={() => setShowCreateModal(false)}>
          <CreateProfileForm
            onSuccess={() => {
              setShowCreateModal(false);
              loadProfiles();
            }}
          />
        </Modal>
      )}
    </div>
  );
}
```

---

## Step 7: Start Watching

After completing onboarding, redirect to the browse page.

```javascript
function OnboardingComplete({ profile }) {
  useEffect(() => {
    // Set up initial state
    localStorage.setItem('onboarding_complete', 'true');
    
    // Redirect to browse after 2 seconds
    setTimeout(() => {
      window.location.href = '/browse';
    }, 2000);
  }, []);

  return (
    <div className="onboarding-complete">
      <div className="success-icon">🎉</div>
      <h1>Welcome to UBIQ Entertainment!</h1>
      <p>Your account is ready. Let's start watching!</p>
      
      <div className="profile-preview">
        <img src={`/images/avatars/${profile.avatar}`} alt={profile.name} />
        <h3>{profile.name}</h3>
      </div>

      <div className="loading-indicator">
        <div className="spinner"></div>
        <p>Loading your personalized experience...</p>
      </div>
    </div>
  );
}
```

---

## Complete Implementation Examples

### Full Onboarding Flow Component

```javascript
import { useState } from 'react';

function OnboardingFlow() {
  const [step, setStep] = useState(1);
  const [userData, setUserData] = useState(null);
  const [selectedPlan, setSelectedPlan] = useState(null);
  const [subscription, setSubscription] = useState(null);
  const [profile, setProfile] = useState(null);

  const steps = [
    { number: 1, title: 'Create Account', component: RegistrationForm },
    { number: 2, title: 'Verify Email', component: EmailVerificationPrompt },
    { number: 3, title: 'Choose Plan', component: SubscriptionPlans },
    { number: 4, title: 'Payment', component: PaymentSetup },
    { number: 5, title: 'Create Profile', component: CreateProfileStep },
    { number: 6, title: 'Complete', component: OnboardingComplete }
  ];

  const CurrentStepComponent = steps[step - 1].component;

  return (
    <div className="onboarding-flow">
      {/* Progress Bar */}
      <div className="progress-bar">
        <div className="progress-steps">
          {steps.map(s => (
            <div
              key={s.number}
              className={`step ${step === s.number ? 'active' : ''} ${step > s.number ? 'completed' : ''}`}
            >
              <div className="step-number">{s.number}</div>
              <div className="step-title">{s.title}</div>
            </div>
          ))}
        </div>
        <div className="progress-fill" style={{ width: `${(step / steps.length) * 100}%` }} />
      </div>

      {/* Current Step Content */}
      <div className="step-content">
        {step === 1 && (
          <RegistrationForm
            onSuccess={(data) => {
              setUserData(data);
              setStep(2);
            }}
          />
        )}

        {step === 2 && (
          <EmailVerificationPrompt
            user={userData.user}
            onVerified={() => setStep(3)}
          />
        )}

        {step === 3 && (
          <SubscriptionPlans
            onSelectPlan={(plan) => {
              setSelectedPlan(plan);
              setStep(4);
            }}
          />
        )}

        {step === 4 && (
          <PaymentSetup
            plan={selectedPlan}
            onSuccess={(sub) => {
              setSubscription(sub);
              setStep(5);
            }}
          />
        )}

        {step === 5 && (
          <CreateProfileStep
            onSuccess={(prof) => {
              setProfile(prof);
              setStep(6);
            }}
          />
        )}

        {step === 6 && <OnboardingComplete profile={profile} />}
      </div>

      {/* Navigation */}
      {step > 1 && step < 6 && (
        <button
          onClick={() => setStep(step - 1)}
          className="btn-back"
        >
          ← Back
        </button>
      )}
    </div>
  );
}

export default OnboardingFlow;
```

### API Call Helper Functions

```javascript
// api.js - Centralized API calls

const API_BASE = 'https://your-domain.com/api';

export const api = {
  // Registration
  register: async (userData) => {
    const response = await fetch(`${API_BASE}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(userData)
    });
    return response.json();
  },

  // Email Verification
  resendVerification: async (token) => {
    const response = await fetch(`${API_BASE}/email/verification-notification`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  },

  // Subscription Plans
  getPlans: async () => {
    const response = await fetch(`${API_BASE}/subscription-plans`);
    return response.json();
  },

  // Create Subscription
  createSubscription: async (token, subscriptionData) => {
    const response = await fetch(`${API_BASE}/subscriptions`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(subscriptionData)
    });
    return response.json();
  },

  // Profiles
  getProfiles: async (token) => {
    const response = await fetch(`${API_BASE}/profiles`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  },

  createProfile: async (token, profileData) => {
    const response = await fetch(`${API_BASE}/profiles`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(profileData)
    });
    return response.json();
  },

  updateProfile: async (token, profileId, profileData) => {
    const response = await fetch(`${API_BASE}/profiles/${profileId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(profileData)
    });
    return response.json();
  },

  deleteProfile: async (token, profileId) => {
    const response = await fetch(`${API_BASE}/profiles/${profileId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.json();
  }
};
```

---

## Summary

### Complete Onboarding Checklist

- ✅ **Step 1:** User registers with email and password
- ✅ **Step 2:** User verifies email address
- ✅ **Step 3:** User selects subscription plan
- ✅ **Step 4:** User enters payment information
- ✅ **Step 5:** User creates first profile
- ✅ **Step 6:** Redirect to browse content

### Key Points

1. **Trial Period:** All plans include a free trial (7-14 days)
2. **No Charge During Trial:** Users won't be charged until trial ends
3. **Multiple Profiles:** Users can create up to plan limit
4. **Profile Types:** Regular and Kids profiles available
5. **Preferences:** Each profile has individual preferences
6. **Easy Management:** Users can add/edit/delete profiles anytime

---

**Related Documentation:**
- [Authentication APIs](./AUTH_APIS.md)
- [Subscription APIs](./SUBSCRIPTION_APIS.md)
- [Profile APIs](./PROFILE_APIS.md)
- [API Overview](./API_OVERVIEW.md)
