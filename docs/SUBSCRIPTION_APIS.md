# Subscription APIs

Subscription management, billing, and payment endpoints.

---

## Table of Contents

1. [Get Subscription Plans](#get-subscription-plans)
2. [Get Current Subscription](#get-current-subscription)
3. [Subscribe to Plan](#subscribe-to-plan)
4. [Update Subscription](#update-subscription)
5. [Cancel Subscription](#cancel-subscription)
6. [Resume Subscription](#resume-subscription)
7. [Update Payment Method](#update-payment-method)
8. [Get Payment Methods](#get-payment-methods)
9. [Get Billing History](#get-billing-history)
10. [Get Invoice](#get-invoice)

---

## Get Subscription Plans

Get all available subscription plans.

### Endpoint
```
GET /api/subscription-plans
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| active | boolean | No | Filter active plans (default: true) |
| interval | string | No | Filter by interval: monthly, yearly, lifetime |

### Success Response (200 OK)

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
        "Limited catalog access",
        "Ads included"
      ],
      "max_concurrent_streams": 1,
      "max_profiles": 2,
      "video_quality": "hd",
      "has_ads": true,
      "is_active": true,
      "created_at": "2025-01-01T00:00:00.000000Z"
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
      "is_active": true,
      "created_at": "2025-01-01T00:00:00.000000Z"
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
        "Early access to new releases",
        "Premium support"
      ],
      "max_concurrent_streams": 4,
      "max_profiles": 10,
      "video_quality": "ultra_hd",
      "has_ads": false,
      "is_active": true,
      "created_at": "2025-01-01T00:00:00.000000Z"
    },
    {
      "id": 4,
      "name": "Yearly Premium",
      "slug": "yearly-premium",
      "description": "Save 20% with annual billing",
      "price": 191.88,
      "currency": "USD",
      "interval": "yearly",
      "trial_days": 30,
      "features": [
        "All Premium features",
        "20% discount (2 months free)",
        "Priority support"
      ],
      "max_concurrent_streams": 4,
      "max_profiles": 10,
      "video_quality": "ultra_hd",
      "has_ads": false,
      "is_active": true,
      "monthly_equivalent": 15.99,
      "created_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Frontend Example

```javascript
async function loadSubscriptionPlans() {
  try {
    const response = await fetch('https://your-domain.com/api/subscription-plans', {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to load plans:', error);
    throw error;
  }
}

// React component
function PricingPlans() {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    loadSubscriptionPlans()
      .then(setPlans)
      .finally(() => setLoading(false));
  }, []);
  
  if (loading) return <Spinner />;
  
  return (
    <div className="pricing-grid">
      {plans.map(plan => (
        <PlanCard key={plan.id} plan={plan} />
      ))}
    </div>
  );
}

function PlanCard({ plan }) {
  return (
    <div className="plan-card">
      <h3>{plan.name}</h3>
      <p className="description">{plan.description}</p>
      <div className="price">
        <span className="amount">${plan.price}</span>
        <span className="interval">/{plan.interval}</span>
      </div>
      {plan.trial_days > 0 && (
        <div className="trial-badge">{plan.trial_days} days free trial</div>
      )}
      <ul className="features">
        {plan.features.map((feature, index) => (
          <li key={index}>{feature}</li>
        ))}
      </ul>
      <button onClick={() => subscribeToPlan(plan.id)}>
        Choose {plan.name}
      </button>
    </div>
  );
}
```

---

## Get Current Subscription

Get the authenticated user's current subscription details.

### Endpoint
```
GET /api/subscription
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Success Response (200 OK)

**Active subscription:**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "user_id": 1,
    "plan_id": 2,
    "status": "active",
    "started_at": "2025-01-01T00:00:00.000000Z",
    "current_period_start": "2025-11-01T00:00:00.000000Z",
    "current_period_end": "2025-12-01T00:00:00.000000Z",
    "trial_ends_at": null,
    "cancelled_at": null,
    "ends_at": null,
    "plan": {
      "id": 2,
      "name": "Standard",
      "price": 14.99,
      "currency": "USD",
      "interval": "monthly",
      "max_concurrent_streams": 2,
      "max_profiles": 5,
      "video_quality": "full_hd"
    },
    "is_on_trial": false,
    "is_active": true,
    "is_cancelled": false,
    "days_until_renewal": 15
  }
}
```

**Trial subscription:**
```json
{
  "success": true,
  "data": {
    "id": 43,
    "status": "trialing",
    "started_at": "2025-11-10T00:00:00.000000Z",
    "trial_ends_at": "2025-11-24T00:00:00.000000Z",
    "plan": {
      "name": "Premium",
      "price": 19.99
    },
    "is_on_trial": true,
    "trial_days_remaining": 8
  }
}
```

**No subscription:**
```json
{
  "success": true,
  "data": null,
  "message": "No active subscription"
}
```

---

## Subscribe to Plan

Subscribe the authenticated user to a plan.

### Endpoint
```
POST /api/subscriptions
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "plan_id": 2,
  "payment_method_id": "pm_1234567890",
  "use_trial": true
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| plan_id | integer | Yes | Subscription plan ID |
| payment_method_id | string | Yes | Stripe payment method ID |
| use_trial | boolean | No | Use trial period if available (default: true) |

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Subscription created successfully",
  "data": {
    "id": 44,
    "user_id": 1,
    "plan_id": 2,
    "status": "trialing",
    "started_at": "2025-11-16T18:00:00.000000Z",
    "trial_ends_at": "2025-11-30T18:00:00.000000Z",
    "current_period_end": "2025-12-16T18:00:00.000000Z",
    "plan": {
      "name": "Standard",
      "price": 14.99,
      "interval": "monthly"
    }
  }
}
```

### Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "plan_id": ["The selected plan is invalid."],
    "payment_method_id": ["Payment method verification failed."]
  }
}
```

### Frontend Example with Stripe

```javascript
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe('pk_test_your_publishable_key');

function SubscribeForm({ planId }) {
  return (
    <Elements stripe={stripePromise}>
      <CheckoutForm planId={planId} />
    </Elements>
  );
}

function CheckoutForm({ planId }) {
  const stripe = useStripe();
  const elements = useElements();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setError(null);
    
    if (!stripe || !elements) {
      return;
    }
    
    try {
      // Create payment method
      const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
        type: 'card',
        card: elements.getElement(CardElement),
        billing_details: {
          email: user.email,
          name: user.name
        }
      });
      
      if (stripeError) {
        setError(stripeError.message);
        return;
      }
      
      // Subscribe with payment method
      const token = localStorage.getItem('token');
      const response = await fetch('https://your-domain.com/api/subscriptions', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          plan_id: planId,
          payment_method_id: paymentMethod.id,
          use_trial: true
        })
      });
      
      const data = await response.json();
      
      if (data.success) {
        showSuccess('Subscription created! Redirecting...');
        setTimeout(() => {
          window.location.href = '/dashboard';
        }, 2000);
      } else {
        setError(data.message);
      }
    } catch (error) {
      setError('Subscription failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <CardElement 
        options={{
          style: {
            base: {
              fontSize: '16px',
              color: '#424770',
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
      {error && <div className="error-message">{error}</div>}
      <button type="submit" disabled={!stripe || loading}>
        {loading ? 'Processing...' : 'Subscribe'}
      </button>
    </form>
  );
}
```

---

## Update Subscription

Upgrade or downgrade to a different plan.

### Endpoint
```
PUT /api/subscriptions/{id}
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "plan_id": 3,
  "prorate": true
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| plan_id | integer | Yes | New subscription plan ID |
| prorate | boolean | No | Prorate charges (default: true) |

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Subscription updated successfully",
  "data": {
    "id": 44,
    "plan_id": 3,
    "status": "active",
    "plan": {
      "name": "Premium",
      "price": 19.99
    },
    "proration_amount": 5.00,
    "next_billing_date": "2025-12-01T00:00:00.000000Z"
  }
}
```

---

## Cancel Subscription

Cancel the subscription (continues until end of billing period).

### Endpoint
```
DELETE /api/subscriptions/{id}
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| immediate | boolean | No | Cancel immediately (default: false) |

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Subscription cancelled successfully",
  "data": {
    "id": 44,
    "status": "cancelled",
    "cancelled_at": "2025-11-16T18:00:00.000000Z",
    "ends_at": "2025-12-01T00:00:00.000000Z",
    "message": "Your subscription will remain active until December 1, 2025"
  }
}
```

### Frontend Example

```javascript
async function cancelSubscription(subscriptionId, immediate = false) {
  const token = localStorage.getItem('token');
  
  const confirmed = confirm(
    immediate 
      ? 'Cancel subscription immediately? You will lose access right away.'
      : 'Cancel subscription? You can use it until the end of your billing period.'
  );
  
  if (!confirmed) return;
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/subscriptions/${subscriptionId}?immediate=${immediate}`,
      {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    
    if (data.success) {
      showSuccess(data.message);
      refreshSubscription();
    } else {
      showError(data.message);
    }
  } catch (error) {
    showError('Failed to cancel subscription');
  }
}
```

---

## Resume Subscription

Resume a cancelled subscription before it ends.

### Endpoint
```
POST /api/subscriptions/{id}/resume
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Subscription resumed successfully",
  "data": {
    "id": 44,
    "status": "active",
    "cancelled_at": null,
    "ends_at": null,
    "current_period_end": "2025-12-01T00:00:00.000000Z"
  }
}
```

---

## Update Payment Method

Update the default payment method for subscription.

### Endpoint
```
PUT /api/payment-methods/default
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "payment_method_id": "pm_new_card_123"
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Payment method updated successfully",
  "data": {
    "payment_method": {
      "id": "pm_new_card_123",
      "type": "card",
      "card": {
        "brand": "visa",
        "last4": "4242",
        "exp_month": 12,
        "exp_year": 2027
      }
    }
  }
}
```

---

## Get Payment Methods

Get all saved payment methods.

### Endpoint
```
GET /api/payment-methods
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": "pm_1234567890",
      "type": "card",
      "card": {
        "brand": "visa",
        "last4": "4242",
        "exp_month": 12,
        "exp_year": 2027
      },
      "is_default": true,
      "created_at": "2025-01-01T00:00:00.000000Z"
    },
    {
      "id": "pm_0987654321",
      "type": "card",
      "card": {
        "brand": "mastercard",
        "last4": "5555",
        "exp_month": 6,
        "exp_year": 2026
      },
      "is_default": false,
      "created_at": "2024-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

## Get Billing History

Get billing and invoice history.

### Endpoint
```
GET /api/billing/history
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| per_page | integer | No | Items per page (default: 15, max: 100) |

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": "inv_123",
      "invoice_number": "INV-2025-001",
      "amount": 14.99,
      "currency": "USD",
      "status": "paid",
      "plan_name": "Standard",
      "billing_period": "November 2025",
      "paid_at": "2025-11-01T00:00:00.000000Z",
      "invoice_url": "https://your-domain.com/api/billing/invoices/inv_123/download"
    },
    {
      "id": "inv_122",
      "invoice_number": "INV-2025-002",
      "amount": 14.99,
      "currency": "USD",
      "status": "paid",
      "plan_name": "Standard",
      "billing_period": "October 2025",
      "paid_at": "2025-10-01T00:00:00.000000Z",
      "invoice_url": "https://your-domain.com/api/billing/invoices/inv_122/download"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 36
  }
}
```

---

## Get Invoice

Download or view a specific invoice.

### Endpoint
```
GET /api/billing/invoices/{id}
```

### Headers
```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| format | string | No | Response format: json or pdf (default: json) |

### Success Response (200 OK)

**JSON format:**
```json
{
  "success": true,
  "data": {
    "id": "inv_123",
    "invoice_number": "INV-2025-001",
    "amount": 14.99,
    "currency": "USD",
    "status": "paid",
    "paid_at": "2025-11-01T00:00:00.000000Z",
    "items": [
      {
        "description": "Standard Plan - November 2025",
        "quantity": 1,
        "unit_price": 14.99,
        "amount": 14.99
      }
    ],
    "subtotal": 14.99,
    "tax": 0.00,
    "total": 14.99,
    "billing_details": {
      "name": "John Doe",
      "email": "john@example.com",
      "address": {
        "line1": "123 Main St",
        "city": "New York",
        "state": "NY",
        "postal_code": "10001",
        "country": "US"
      }
    },
    "payment_method": {
      "type": "card",
      "brand": "visa",
      "last4": "4242"
    }
  }
}
```

**PDF format:** Returns PDF file for download.

---

**Back to:** [API Overview ←](./API_OVERVIEW.md)
