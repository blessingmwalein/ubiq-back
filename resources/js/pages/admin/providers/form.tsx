import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { router } from '@inertiajs/react';
import { ContentProvider, User } from '@/types/streaming';
import { Save, ArrowLeft } from 'lucide-react';

interface ProviderFormProps {
    provider?: ContentProvider & { content_items?: any[]; content_items_count?: number };
    users: User[];
}

export default function ProviderForm({ provider, users }: ProviderFormProps) {
    const [formData, setFormData] = useState({
        owner_id: provider?.owner_id || '',
        display_name: provider?.display_name || '',
        contact_email: provider?.contact_email || '',
        contact_phone: provider?.contact_phone || '',
        description: provider?.description || '',
        logo_url: provider?.logo_url || '',
        status: provider?.status || 'pending',
        revenue_share_percentage: provider?.revenue_share_percentage || 70,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (provider) {
            router.put(`/admin/providers/${provider.id}`, formData);
        } else {
            router.post('/admin/providers', formData);
        }
    };

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
    ) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    return (
        <AdminLayout
            title={provider ? 'Edit Provider' : 'Create Provider'}
            header={
                <div>
                    <div className="flex items-center space-x-4 mb-2">
                        <button
                            onClick={() => router.visit('/admin/providers')}
                            className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="w-6 h-6" />
                        </button>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            {provider ? 'Edit Content Provider' : 'Create New Content Provider'}
                        </h1>
                    </div>
                </div>
            }
        >
            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Provider Information
                    </h3>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Owner (User Account) *
                            </label>
                            <select
                                name="owner_id"
                                value={formData.owner_id}
                                onChange={handleChange}
                                required
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">Select a user...</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name} ({user.email})
                                    </option>
                                ))}
                            </select>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                The user account that owns this provider
                            </p>
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Display Name *
                            </label>
                            <input
                                type="text"
                                name="display_name"
                                value={formData.display_name}
                                onChange={handleChange}
                                required
                                placeholder="e.g., Netflix Studios, HBO Max"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Email *
                            </label>
                            <input
                                type="email"
                                name="contact_email"
                                value={formData.contact_email}
                                onChange={handleChange}
                                required
                                placeholder="contact@provider.com"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Phone
                            </label>
                            <input
                                type="tel"
                                name="contact_phone"
                                value={formData.contact_phone}
                                onChange={handleChange}
                                placeholder="+1 (555) 123-4567"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                rows={4}
                                placeholder="Brief description of the content provider..."
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Logo URL
                            </label>
                            <input
                                type="url"
                                name="logo_url"
                                value={formData.logo_url}
                                onChange={handleChange}
                                placeholder="https://example.com/logo.png"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {formData.logo_url && (
                                <div className="mt-2">
                                    <img 
                                        src={formData.logo_url} 
                                        alt="Logo preview" 
                                        className="w-24 h-24 rounded-lg object-cover"
                                    />
                                </div>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status *
                            </label>
                            <select
                                name="status"
                                value={formData.status}
                                onChange={handleChange}
                                required
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="suspended">Suspended</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Only approved providers can add content
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Revenue Share (%) *
                            </label>
                            <input
                                type="number"
                                name="revenue_share_percentage"
                                value={formData.revenue_share_percentage}
                                onChange={handleChange}
                                required
                                min="0"
                                max="100"
                                step="0.01"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Percentage of revenue this provider receives
                            </p>
                        </div>

                        {provider && provider.content_items_count !== undefined && (
                            <div className="md:col-span-2">
                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <p className="text-sm text-blue-800 dark:text-blue-200">
                                        This provider currently has <strong>{provider.content_items_count}</strong> content item(s).
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={() => router.visit('/admin/providers')}
                        className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Save className="w-5 h-5 mr-2" />
                        {provider ? 'Update Provider' : 'Create Provider'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
