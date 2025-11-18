import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { router } from '@inertiajs/react';
import { Category } from '@/types/streaming';
import { Save, ArrowLeft } from 'lucide-react';

interface CategoryFormProps {
    category?: Category & { content_items_count?: number };
}

export default function CategoryForm({ category }: CategoryFormProps) {
    const [formData, setFormData] = useState({
        key: category?.key || '',
        title: category?.title || '',
        description: category?.description || '',
        icon_url: category?.icon_url || '',
        sort_order: category?.sort_order || 0,
        is_active: category?.is_active ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (category) {
            router.put(`/admin/categories/${category.id}`, formData);
        } else {
            router.post('/admin/categories', formData);
        }
    };

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => {
        const value = e.target.type === 'checkbox' 
            ? (e.target as HTMLInputElement).checked 
            : e.target.value;

        setFormData({
            ...formData,
            [e.target.name]: value,
        });
    };

    return (
        <AdminLayout
            title={category ? 'Edit Category' : 'Create Category'}
            header={
                <div>
                    <div className="flex items-center space-x-4 mb-2">
                        <button
                            onClick={() => router.visit('/admin/categories')}
                            className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="w-6 h-6" />
                        </button>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            {category ? 'Edit Category' : 'Create New Category'}
                        </h1>
                    </div>
                </div>
            }
        >
            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Category Information
                    </h3>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title *
                            </label>
                            <input
                                type="text"
                                name="title"
                                value={formData.title}
                                onChange={handleChange}
                                required
                                placeholder="e.g., Action, Drama, Comedy"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Key *
                            </label>
                            <input
                                type="text"
                                name="key"
                                value={formData.key}
                                onChange={handleChange}
                                required
                                placeholder="e.g., action, drama, comedy"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Unique identifier (lowercase, no spaces, use underscores)
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
                                placeholder="Brief description of this category..."
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Icon URL
                            </label>
                            <input
                                type="url"
                                name="icon_url"
                                value={formData.icon_url}
                                onChange={handleChange}
                                placeholder="https://example.com/icon.svg"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            {formData.icon_url && (
                                <div className="mt-2">
                                    <img 
                                        src={formData.icon_url} 
                                        alt="Icon preview" 
                                        className="w-12 h-12 rounded object-cover"
                                    />
                                </div>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sort Order
                            </label>
                            <input
                                type="number"
                                name="sort_order"
                                value={formData.sort_order}
                                onChange={handleChange}
                                min="0"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Lower numbers appear first
                            </p>
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
                                    Active (visible to users)
                                </span>
                            </label>
                        </div>

                        {category && category.content_items_count !== undefined && (
                            <div className="md:col-span-2">
                                <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <p className="text-sm text-blue-800 dark:text-blue-200">
                                        This category is currently used by <strong>{category.content_items_count}</strong> content item(s).
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={() => router.visit('/admin/categories')}
                        className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Save className="w-5 h-5 mr-2" />
                        {category ? 'Update Category' : 'Create Category'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
