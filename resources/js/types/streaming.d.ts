export interface Package {
    id: string;
    key: string;
    title: string;
    description?: string;
    price_monthly: number;
    price_yearly: number;
    trial_days: number;
    max_profiles: number;
    features: string[];
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ContentItem {
    id: string;
    title: string;
    slug?: string;
    description: string;
    type: 'movie' | 'show' | 'skit' | 'afrimation' | 'real_estate';
    category_id: string;
    category?: Category;
    provider_id: string;
    content_provider?: ContentProvider;
    show_id?: string;
    show?: Show;
    poster_url?: string;
    backdrop_url?: string;
    thumbnail_url?: string;
    trailer_url?: string;
    visibility: 'public' | 'private' | 'draft';
    maturity_rating: 'all' | 'pg' | 'pg13' | 'r' | 'adult';
    release_year?: number;
    duration_seconds?: number;
    views_count: number;
    metadata?: Record<string, any>;
    published_at?: string;
    video_assets?: VideoAsset[];
    created_at: string;
    updated_at: string;
}

export interface VideoAsset {
    id: string;
    uuid: string;
    episode_id?: string;
    content_item_id?: string;
    rendition_key: string;
    hls_manifest_key: string;
    hls_playlist_path?: string;
    bitrate?: number;
    resolution?: string;
    file_size_mb?: number;
    status: 'processing' | 'ready' | 'failed';
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: string;
    key: string;
    title: string;
    description?: string;
    icon_url?: string;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ContentProvider {
    id: string;
    owner_id: string;
    display_name: string;
    contact_email: string;
    contact_phone?: string;
    description?: string;
    logo_url?: string;
    status: 'pending' | 'approved' | 'suspended' | 'rejected';
    revenue_share_percentage: number;
    created_at: string;
    updated_at: string;
}

export interface Show {
    id: string;
    uuid: string;
    content_item_id: string;
    content_item?: ContentItem;
    total_seasons: number;
    total_episodes: number;
    status: 'ongoing' | 'completed' | 'cancelled';
    seasons?: Season[];
    created_at: string;
    updated_at: string;
}

export interface Season {
    id: string;
    uuid: string;
    show_id: string;
    show?: Show;
    season_number: number;
    title?: string;
    description?: string;
    poster_url?: string | null | File;
    episode_count: number;
    episodes?: Episode[];
    released_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Episode {
    id: string;
    uuid: string;
    season_id: string;
    season?: Season;
    content_item_id: string;
    content_item?: ContentItem;
    episode_number: number;
    title: string;
    description?: string;
    duration_seconds?: number;
    thumbnail_url?: string | null | File;
    video_master_key?: string;
    views_count: number;
    released_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Subscription {
    id: string;
    account_id: string;
    package_id: string;
    package?: Package;
    status: 'active' | 'cancelled' | 'expired' | 'suspended';
    current_period_start: string;
    current_period_end: string;
    cancelled_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Payment {
    id: string;
    account_id: string;
    subscription_id?: string;
    amount: number;
    currency: string;
    payment_method: string;
    status: 'pending' | 'completed' | 'failed' | 'refunded';
    transaction_id?: string;
    metadata?: Record<string, any>;
    paid_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Account {
    id: string;
    user_id: string;
    package_id?: string;
    is_primary: boolean;
    status: 'active' | 'suspended' | 'cancelled';
    trial_ends_at?: string;
    profiles_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Profile {
    id: string;
    account_id: string;
    name: string;
    avatar_url?: string;
    maturity_rating: 'G' | 'PG' | 'PG-13' | 'R' | 'NC-17';
    is_primary: boolean;
    created_at: string;
    updated_at: string;
}

export interface VideoAsset {
    id: string;
    content_item_id: string;
    storage_path: string;
    hls_playlist_url?: string;
    file_size: number;
    duration: number;
    resolution: string;
    codec: string;
    bitrate: number;
    status: 'pending' | 'processing' | 'ready' | 'failed';
    created_at: string;
    updated_at: string;
}

export interface WatchHistory {
    id: string;
    profile_id: string;
    content_item_id: string;
    content_item?: ContentItem;
    progress_seconds: number;
    duration_seconds: number;
    completed: boolean;
    last_watched_at: string;
    created_at: string;
    updated_at: string;
}

export interface AnalyticsData {
    totalUsers: number;
    totalSubscriptions: number;
    totalRevenue: number;
    totalContent: number;
    activeUsers: number;
    newUsers: number;
    revenueGrowth: number;
    contentViews: number;
    revenueByMonth: { month: string; revenue: number }[];
    topContent: { title: string; views: number }[];
    subscriptionsByPackage: { package: string; count: number }[];
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}
