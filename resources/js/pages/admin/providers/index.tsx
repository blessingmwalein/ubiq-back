import AdminLayout from '@/layouts/admin-layout';
import { Link, router } from '@inertiajs/react';
import { ContentProvider, User } from '@/types/streaming';
import { Plus, Edit, Trash2, Building2, Mail, Phone, CheckCircle, XCircle, Clock, Ban, Search } from 'lucide-react';
import { Badge } from '@/components/admin/ui-components';
import { useState } from 'react';

interface ProvidersIndexProps {
    providers: {
        data: (ContentProvider & { owner: User; content_items_count?: number })[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

export default function ProvidersIndex({ providers, filters }: ProvidersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleFilter = () => {
        router.get('/admin/providers', { search, status }, { preserveState: true });
    };

    const handleStatusChange = (id: string, newStatus: string) => {
        if (confirm(`Change provider status to ${newStatus}?`)) {
            router.post(`/admin/providers/${id}/status`, { status: newStatus });
        }
    };

    const handleDelete = (id: string, name: string) => {
        if (confirm(`Are you sure you want to delete provider "${name}"? This action cannot be undone.`)) {
            router.delete(`/admin/providers/${id}`);
        }
    };

    const getStatusBadge = (providerStatus: string) => {
        const statusConfig = {
            approved: { variant: 'success' as const, icon: CheckCircle, text: 'Approved' },
            pending: { variant: 'warning' as const, icon: Clock, text: 'Pending' },
            suspended: { variant: 'danger' as const, icon: Ban, text: 'Suspended' },
            rejected: { variant: 'default' as const, icon: XCircle, text: 'Rejected' },
        };

        const config = statusConfig[providerStatus as keyof typeof statusConfig];
        const Icon = config.icon;

        return (
            <Badge variant={config.variant}>
                <Icon className="w-3 h-3 mr-1" />
                {config.text}
            </Badge>
        );
    };

    return (
        <AdminLayout
            title="Content Providers"
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            Content Providers
                        </h1>
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Manage content providers and their accounts
                        </p>
                    </div>
                    <Link
                        href="/admin/providers/create"
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Plus className="w-5 h-5 mr-2" />
                        Add Provider
                    </Link>
                </div>
            }
        >
            {/* Filters */}
            <div className="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search by name or email..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <select
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                            className="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <button
                            onClick={handleFilter}
                            className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                        >
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            {/* Providers Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {providers.data.map((provider) => (
                    <div
                        key={provider.id}
                        className="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden hover:border-indigo-500 transition-colors"
                    >
                        {/* Provider Header */}
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div className="flex items-start justify-between mb-3">
                                <div className="flex items-center space-x-3">
                                    {provider.logo_url ? (
                                        <img
                                            src={provider.logo_url}
                                            alt={provider.display_name}
                                            className="w-12 h-12 rounded-lg object-cover"
                                        />
                                    ) : (
                                        <div className="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                            <Building2 className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                    )}
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                            {provider.display_name}
                                        </h3>
                                        {getStatusBadge(provider.status)}
                                    </div>
                                </div>
                            </div>

                            {provider.description && (
                                <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {provider.description}
                                </p>
                            )}
                        </div>

                        {/* Provider Details */}
                        <div className="p-6 space-y-3">
                            <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <Mail className="w-4 h-4 mr-2 flex-shrink-0" />
                                <a href={`mailto:${provider.contact_email}`} className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {provider.contact_email}
                                </a>
                            </div>

                            {provider.contact_phone && (
                                <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <Phone className="w-4 h-4 mr-2 flex-shrink-0" />
                                    <a href={`tel:${provider.contact_phone}`} className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {provider.contact_phone}
                                    </a>
                                </div>
                            )}

                            <div className="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-600 dark:text-gray-400">Content Items:</span>
                                    <span className="font-medium text-gray-900 dark:text-white">
                                        {provider.content_items_count || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm mt-2">
                                    <span className="text-gray-600 dark:text-gray-400">Revenue Share:</span>
                                    <span className="font-medium text-gray-900 dark:text-white">
                                        {provider.revenue_share_percentage}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                            <div className="flex items-center justify-between">
                                <div className="flex space-x-2">
                                    {provider.status === 'pending' && (
                                        <>
                                            <button
                                                onClick={() => handleStatusChange(provider.id, 'approved')}
                                                className="text-sm text-green-600 hover:text-green-900 dark:text-green-400 font-medium"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                onClick={() => handleStatusChange(provider.id, 'rejected')}
                                                className="text-sm text-red-600 hover:text-red-900 dark:text-red-400 font-medium"
                                            >
                                                Reject
                                            </button>
                                        </>
                                    )}
                                    {provider.status === 'approved' && (
                                        <button
                                            onClick={() => handleStatusChange(provider.id, 'suspended')}
                                            className="text-sm text-orange-600 hover:text-orange-900 dark:text-orange-400 font-medium"
                                        >
                                            Suspend
                                        </button>
                                    )}
                                    {provider.status === 'suspended' && (
                                        <button
                                            onClick={() => handleStatusChange(provider.id, 'approved')}
                                            className="text-sm text-green-600 hover:text-green-900 dark:text-green-400 font-medium"
                                        >
                                            Reactivate
                                        </button>
                                    )}
                                </div>
                                <div className="flex items-center space-x-2">
                                    <Link
                                        href={`/admin/providers/${provider.id}/edit`}
                                        className="p-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        <Edit className="w-5 h-5" />
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(provider.id, provider.display_name)}
                                        className="p-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <Trash2 className="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {providers.data.length === 0 && (
                <div className="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <Building2 className="mx-auto h-12 w-12 text-gray-400" />
                    <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        No providers found
                    </h3>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a new content provider.
                    </p>
                    <div className="mt-6">
                        <Link
                            href="/admin/providers/create"
                            className="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                        >
                            <Plus className="w-5 h-5 mr-2" />
                            Add Provider
                        </Link>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
