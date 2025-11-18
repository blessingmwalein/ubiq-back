import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Subscription, PaginatedResponse, Package } from '@/types/streaming';
import { Link, router } from '@inertiajs/react';
import { Search, Filter, Calendar, CreditCard, User } from 'lucide-react';
import { Badge } from '@/components/admin/ui-components';

interface SubscriptionsIndexProps {
    subscriptions: PaginatedResponse<Subscription & { 
        account?: { user?: { name: string; email: string } };
    }>;
    filters: {
        search?: string;
        status?: string;
        package_id?: string;
    };
    packages: Package[];
}

export default function SubscriptionsIndex({ subscriptions, filters, packages }: SubscriptionsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/subscriptions', { search }, { preserveState: true });
    };

    const getStatusBadgeVariant = (status: string) => {
        switch (status) {
            case 'active':
                return 'success';
            case 'cancelled':
                return 'warning';
            case 'expired':
                return 'danger';
            case 'suspended':
                return 'danger';
            default:
                return 'default';
        }
    };

    return (
        <AdminLayout
            title="Subscriptions"
            header={
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Subscriptions
                    </h1>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Monitor and manage customer subscriptions
                    </p>
                </div>
            }
        >
            {/* Stats Summary */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Active</p>
                            <p className="mt-2 text-3xl font-semibold text-green-600 dark:text-green-400">
                                {subscriptions.data.filter(s => s.status === 'active').length}
                            </p>
                        </div>
                        <div className="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <CreditCard className="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Cancelled</p>
                            <p className="mt-2 text-3xl font-semibold text-yellow-600 dark:text-yellow-400">
                                {subscriptions.data.filter(s => s.status === 'cancelled').length}
                            </p>
                        </div>
                        <div className="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                            <CreditCard className="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Expired</p>
                            <p className="mt-2 text-3xl font-semibold text-red-600 dark:text-red-400">
                                {subscriptions.data.filter(s => s.status === 'expired').length}
                            </p>
                        </div>
                        <div className="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <CreditCard className="w-6 h-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                {subscriptions.total}
                            </p>
                        </div>
                        <div className="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <CreditCard className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700 mb-6">
                <form onSubmit={handleSearch} className="flex gap-4">
                    <div className="flex-1">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by user email..."
                                className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                    <select
                        value={filters.status || ''}
                        onChange={(e) => router.get('/admin/subscriptions', { ...filters, status: e.target.value })}
                        className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <select
                        value={filters.package_id || ''}
                        onChange={(e) => router.get('/admin/subscriptions', { ...filters, package_id: e.target.value })}
                        className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Packages</option>
                        {packages.map((pkg) => (
                            <option key={pkg.id} value={pkg.id}>
                                {pkg.name}
                            </option>
                        ))}
                    </select>
                    <button
                        type="submit"
                        className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        Search
                    </button>
                </form>
            </div>

            {/* Subscriptions Table */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Package
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Period
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Renewal Date
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {subscriptions.data.map((subscription) => (
                                <tr key={subscription.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <User className="w-8 h-8 text-gray-400" />
                                            <div className="ml-4">
                                                <div className="text-sm font-medium text-gray-900 dark:text-white">
                                                    {subscription.account?.user?.name || 'N/A'}
                                                </div>
                                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                                    {subscription.account?.user?.email || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                                            {subscription.package?.name || 'N/A'}
                                        </div>
                                        <div className="text-sm text-gray-500 dark:text-gray-400">
                                            ${subscription.package?.price}/{subscription.package?.billing_cycle}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <Badge variant={getStatusBadgeVariant(subscription.status)}>
                                            {subscription.status}
                                        </Badge>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div className="flex items-center">
                                            <Calendar className="w-3 h-3 mr-1" />
                                            {new Date(subscription.current_period_start).toLocaleDateString()}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div className="flex items-center">
                                            <Calendar className="w-3 h-3 mr-1" />
                                            {new Date(subscription.current_period_end).toLocaleDateString()}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span className="font-medium">{subscriptions.from}</span> to{' '}
                            <span className="font-medium">{subscriptions.to}</span> of{' '}
                            <span className="font-medium">{subscriptions.total}</span> results
                        </div>
                        <div className="flex space-x-2">
                            {subscriptions.current_page > 1 && (
                                <Link
                                    href={`/admin/subscriptions?page=${subscriptions.current_page - 1}`}
                                    className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium"
                                >
                                    Previous
                                </Link>
                            )}
                            {subscriptions.current_page < subscriptions.last_page && (
                                <Link
                                    href={`/admin/subscriptions?page=${subscriptions.current_page + 1}`}
                                    className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium"
                                >
                                    Next
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
