import AdminLayout from '@/layouts/admin-layout';
import { Link, router } from '@inertiajs/react';
import { Category } from '@/types/streaming';
import { Plus, Edit, Trash2, Tag, Eye, EyeOff, GripVertical } from 'lucide-react';
import { Badge } from '@/components/admin/ui-components';

interface CategoriesIndexProps {
    categories: (Category & { content_items_count?: number })[];
}

export default function CategoriesIndex({ categories }: CategoriesIndexProps) {
    const toggleStatus = (id: string, currentStatus: boolean) => {
        if (confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this category?`)) {
            router.post(`/admin/categories/${id}/toggle-status`);
        }
    };

    const handleDelete = (id: string, title: string) => {
        if (confirm(`Are you sure you want to delete the category "${title}"? This action cannot be undone.`)) {
            router.delete(`/admin/categories/${id}`);
        }
    };

    return (
        <AdminLayout
            title="Categories Management"
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            Categories
                        </h1>
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Manage content categories and genres
                        </p>
                    </div>
                    <Link
                        href="/admin/categories/create"
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <Plus className="w-5 h-5 mr-2" />
                        Add Category
                    </Link>
                </div>
            }
        >
            {/* Categories Table */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Order
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Category
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Key
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Content Count
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {categories.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-6 py-12 text-center">
                                        <Tag className="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                            No categories
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Get started by creating a new category.
                                        </p>
                                        <div className="mt-6">
                                            <Link
                                                href="/admin/categories/create"
                                                className="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                                            >
                                                <Plus className="w-5 h-5 mr-2" />
                                                Add Category
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            ) : (
                                categories.map((category) => (
                                    <tr key={category.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center text-gray-500 dark:text-gray-400">
                                                <GripVertical className="w-4 h-4 mr-2" />
                                                <span className="text-sm font-medium">{category.sort_order}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center">
                                                {category.icon_url && (
                                                    <img
                                                        src={category.icon_url}
                                                        alt={category.title}
                                                        className="w-8 h-8 rounded mr-3 object-cover"
                                                    />
                                                )}
                                                <div>
                                                    <div className="text-sm font-medium text-gray-900 dark:text-white">
                                                        {category.title}
                                                    </div>
                                                    {category.description && (
                                                        <div className="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                                            {category.description}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <code className="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-900 dark:text-white">
                                                {category.key}
                                            </code>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm text-gray-900 dark:text-white">
                                                {category.content_items_count || 0} items
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <Badge variant={category.is_active ? 'success' : 'default'}>
                                                {category.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div className="flex items-center justify-end space-x-2">
                                                <button
                                                    onClick={() => toggleStatus(category.id, category.is_active)}
                                                    className="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                                    title={category.is_active ? 'Deactivate' : 'Activate'}
                                                >
                                                    {category.is_active ? (
                                                        <EyeOff className="w-5 h-5" />
                                                    ) : (
                                                        <Eye className="w-5 h-5" />
                                                    )}
                                                </button>
                                                <Link
                                                    href={`/admin/categories/${category.id}/edit`}
                                                    className="p-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    <Edit className="w-5 h-5" />
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(category.id, category.title)}
                                                    className="p-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                >
                                                    <Trash2 className="w-5 h-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
