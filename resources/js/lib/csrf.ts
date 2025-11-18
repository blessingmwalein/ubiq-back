/**
 * Get the CSRF token from the meta tag or cookie
 */
export function getCsrfToken(): string {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        const token = metaTag.getAttribute('content') || '';
        if (token) {
            console.log('CSRF token found in meta tag');
            return token;
        }
    }

    // Fallback to cookie (XSRF-TOKEN is encrypted, we need X-XSRF-TOKEN)
    const xsrfCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='));
    
    if (xsrfCookie) {
        const token = decodeURIComponent(xsrfCookie.split('=')[1]);
        console.log('CSRF token found in XSRF-TOKEN cookie');
        return token;
    }

    console.error('CSRF token not found! Make sure <meta name="csrf-token"> is in your HTML.');
    return '';
}

/**
 * Get headers with CSRF token for fetch requests
 */
export function getCsrfHeaders(): HeadersInit {
    const token = getCsrfToken();
    return {
        'X-CSRF-TOKEN': token,
        'X-XSRF-TOKEN': token, // Some Laravel setups use this
        'Accept': 'application/json',
    };
}
