import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { Package } from '@/types/streaming';
import { Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Check, X, DollarSign } from 'lucide-react';
import { Badge } from '@/components/admin/ui-components';

interface PackagesIndexProps {
    packages: Package[];
}

export default function PackagesIndex({ packages }: PackagesIndexProps) {
    const handleDelete = (id: string, name: string) => {
        if (confirm(`Are you sure you want to delete package "${name}"?`)) {
            router.delete(`/admin/packages/${id}`);
        }
    };

    const toggleStatus = (id: string, isActive: boolean) => {
        router.post(
            `/admin/packages/${id}/toggle-status`,
            {},
            { preserveState: true }
        );
    };

    return (
        <AdminLayout
            title="Packages Management"
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            Subscription Packages
                        </h1>
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Manage subscription tiers and pricing
                        </p>
                    </div>
                    <Link
                        href="/admin/packages/create"
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Plus className="w-5 h-5 mr-2" />
                        Add Package
                    </Link>
                </div>
            }
        >
            {/* Packages Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {packages.map((pkg) => (
                    <div
                        key={pkg.id}
                        className="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-gray-200 dark:border-gray-700 overflow-hidden hover:border-indigo-500 transition-colors"
                    >
                        {/* Package Header */}
                        <div className="p-6 bg-gradient-to-r from-indigo-500 to-purple-600">
                            <div className="flex items-center justify-between mb-2">
                                <h3 className="text-2xl font-bold text-white">{pkg.title}</h3>
                                <Badge variant={pkg.is_active ? 'success' : 'default'}>
                                    {pkg.is_active ? 'Active' : 'Inactive'}
                                </Badge>
                            </div>
                            <div className="space-y-1">
                                <div className="flex items-baseline text-white">
                                    <span className="text-4xl font-extrabold">${pkg.price_monthly}</span>
                                    <span className="ml-2 text-lg">/month</span>
                                </div>
                                <div className="flex items-baseline text-white/80 text-sm">
                                    <span className="text-2xl font-bold">${pkg.price_yearly}</span>
                                    <span className="ml-2">/year</span>
                                </div>
                            </div>
                        </div>

                        {/* Package Details */}
                        <div className="p-6">
                            {pkg.description && (
                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {pkg.description}
                                </p>
                            )}

                            <div className="space-y-3 mb-6">
                                <div className="flex items-center text-sm">
                                    <Check className="w-4 h-4 text-green-500 mr-2" />
                                    <span className="text-gray-700 dark:text-gray-300">
                                        {pkg.max_profiles} {pkg.max_profiles === 1 ? 'Profile' : 'Profiles'}
                                    </span>
                                </div>
                                {pkg.trial_days > 0 && (
                                    <div className="flex items-center text-sm">
                                        <Check className="w-4 h-4 text-green-500 mr-2" />
                                        <span className="text-gray-700 dark:text-gray-300">
                                            {pkg.trial_days} Days Free Trial
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Features List */}
                            {pkg.features && pkg.features.length > 0 && (
                                <div className="mb-6">
                                    <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Features:
                                    </h4>
                                    <ul className="space-y-2">
                                        {pkg.features.map((feature, index) => (
                                            <li key={index} className="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                                <Check className="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>{feature}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {/* Actions */}
                            <div className="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    onClick={() => toggleStatus(pkg.id, pkg.is_active)}
                                    className={`text-sm font-medium ${
                                        pkg.is_active
                                            ? 'text-red-600 hover:text-red-900 dark:text-red-400'
                                            : 'text-green-600 hover:text-green-900 dark:text-green-400'
                                    }`}
                                >
                                    {pkg.is_active ? 'Deactivate' : 'Activate'}
                                </button>
                                <div className="flex items-center space-x-2">
                                    <Link
                                        href={`/admin/packages/${pkg.id}/edit`}
                                        className="p-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        <Edit className="w-5 h-5" />
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(pkg.id, pkg.title)}
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
            {packages.length === 0 && (
                <div className="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <DollarSign className="mx-auto h-12 w-12 text-gray-400" />
                    <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        No packages
                    </h3>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a new subscription package.
                    </p>
                    <div className="mt-6">
                        <Link
                            href="/admin/packages/create"
                            className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            <Plus className="w-5 h-5 mr-2" />
                            Add Package
                        </Link>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
