import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { router } from '@inertiajs/react';
import { Package } from '@/types/streaming';
import { Save, ArrowLeft, Plus, X } from 'lucide-react';

interface PackageFormProps {
    package?: Package;
}

export default function PackageForm({ package: pkg }: PackageFormProps) {
    const [formData, setFormData] = useState({
        title: pkg?.title || '',
        key: pkg?.key || '',
        description: pkg?.description || '',
        price_monthly: pkg?.price_monthly || 0,
        price_yearly: pkg?.price_yearly || 0,
        trial_days: pkg?.trial_days || 0,
        max_profiles: pkg?.max_profiles || 1,
        is_active: pkg?.is_active ?? true,
    });

    const [features, setFeatures] = useState<string[]>(pkg?.features || []);
    const [newFeature, setNewFeature] = useState('');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        const data = {
            ...formData,
            features: features,
        };

        if (pkg) {
            router.put(`/admin/packages/${pkg.id}`, data);
        } else {
            router.post('/admin/packages', data);
        }
    };

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
    ) => {
        const value = e.target.type === 'checkbox' 
            ? (e.target as HTMLInputElement).checked 
            : e.target.value;

        setFormData({
            ...formData,
            [e.target.name]: value,
        });
    };

    const addFeature = () => {
        if (newFeature.trim()) {
            setFeatures([...features, newFeature.trim()]);
            setNewFeature('');
        }
    };

    const removeFeature = (index: number) => {
        setFeatures(features.filter((_, i) => i !== index));
    };

    return (
        <AdminLayout
            title={pkg ? 'Edit Package' : 'Create Package'}
            header={
                <div>
                    <div className="flex items-center space-x-4 mb-2">
                        <button
                            onClick={() => router.visit('/admin/packages')}
                            className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="w-6 h-6" />
                        </button>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            {pkg ? 'Edit Package' : 'Create New Package'}
                        </h1>
                    </div>
                </div>
            }
        >
            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Package Information
                    </h3>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Package Title *
                            </label>
                            <input
                                type="text"
                                name="title"
                                value={formData.title}
                                onChange={handleChange}
                                required
                                placeholder="e.g., Basic, Standard, Premium"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Package Key *
                            </label>
                            <input
                                type="text"
                                name="key"
                                value={formData.key}
                                onChange={handleChange}
                                required
                                placeholder="e.g., basic, standard, premium"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Unique identifier for this package (lowercase, no spaces)
                            </p>
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                rows={3}
                                placeholder="Describe what this package includes..."
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Monthly Price ($) *
                            </label>
                            <input
                                type="number"
                                name="price_monthly"
                                value={formData.price_monthly}
                                onChange={handleChange}
                                required
                                min="0"
                                step="0.01"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Yearly Price ($) *
                            </label>
                            <input
                                type="number"
                                name="price_yearly"
                                value={formData.price_yearly}
                                onChange={handleChange}
                                required
                                min="0"
                                step="0.01"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Trial Days
                            </label>
                            <input
                                type="number"
                                name="trial_days"
                                value={formData.trial_days}
                                onChange={handleChange}
                                min="0"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Max Profiles *
                            </label>
                            <input
                                type="number"
                                name="max_profiles"
                                value={formData.max_profiles}
                                onChange={handleChange}
                                required
                                min="1"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    checked={formData.is_active}
                                    onChange={handleChange}
                                    className="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                                />
                                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Active (available for subscription)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Features */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Features
                    </h3>

                    <div className="space-y-4">
                        <div className="flex gap-2">
                            <input
                                type="text"
                                value={newFeature}
                                onChange={(e) => setNewFeature(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addFeature())}
                                placeholder="Add a feature..."
                                className="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <button
                                type="button"
                                onClick={addFeature}
                                className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
                            >
                                <Plus className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="space-y-2">
                            {features.map((feature, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <span className="text-sm text-gray-900 dark:text-white">{feature}</span>
                                    <button
                                        type="button"
                                        onClick={() => removeFeature(index)}
                                        className="text-red-600 hover:text-red-900 dark:text-red-400"
                                    >
                                        <X className="w-4 h-4" />
                                    </button>
                                </div>
                            ))}
                            {features.length === 0 && (
                                <p className="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    No features added yet. Add features to highlight what's included in this package.
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={() => router.visit('/admin/packages')}
                        className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Save className="w-5 h-5 mr-2" />
                        {pkg ? 'Update Package' : 'Create Package'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
