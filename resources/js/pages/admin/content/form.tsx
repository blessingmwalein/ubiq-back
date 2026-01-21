import { useState } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import { router } from '@inertiajs/react';
import { ContentItem, Category, ContentProvider } from '@/types/streaming';
import { Save, ArrowLeft } from 'lucide-react';
import { ImageUpload } from '@/components/ui/image-upload';
import { VideoUpload } from '@/components/ui/video-upload';
import { DatePicker } from '@/components/ui/date-picker';
import { MultiSelect } from '@/components/ui/multi-select';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast';
import { getCsrfHeaders } from '@/lib/csrf';

interface ContentFormProps {
    content?: ContentItem;
    categories: Category[];
    providers: ContentProvider[];
}

export default function ContentForm({ content, categories, providers }: ContentFormProps) {
    const { success, error: showError } = useToast()
    const [isSubmitting, setIsSubmitting] = useState(false)

    // Parse genres from comma-separated string to array
    const contentWithGenre = content as ContentItem & { genre?: string };
    const initialGenres = contentWithGenre?.genre ? contentWithGenre.genre.split(',').map((g: string) => g.trim()) : [];

    const [formData, setFormData] = useState({
        title: content?.title || '',
        description: content?.description || '',
        type: content?.type || 'movie',
        genre: initialGenres,
        category_id: content?.category_id || '',
        provider_id: content?.provider_id || '',
        poster_url: content?.poster_url || '',
        backdrop_url: content?.backdrop_url || '',
        thumbnail_url: content?.thumbnail_url || '',
        trailer_url: content?.trailer_url || '',
        visibility: content?.visibility || 'public',
        maturity_rating: content?.maturity_rating || 'all',
        release_year: content?.release_year || new Date().getFullYear(),
        duration_seconds: content?.duration_seconds || 0,
        published_at: content?.published_at ? new Date(content.published_at).toISOString().split('T')[0] : '',
    });

    const [files, setFiles] = useState({
        poster: null as File | null,
        backdrop: null as File | null,
        thumbnail: null as File | null,
        trailer: null as File | null,
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    const uploadFile = async (file: File, type: 'image' | 'video', folder: string): Promise<string> => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('folder', folder);

        const endpoint = type === 'image' ? '/admin/upload/image' : '/admin/upload/video';

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: getCsrfHeaders(),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Upload failed');
        }

        return data.url;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        try {
            const finalFormData = { ...formData };

            // Convert genre array to comma-separated string
            const genreString = Array.isArray(finalFormData.genre) ? finalFormData.genre.join(', ') : '';
            const submissionData: any = {
                ...finalFormData,
                genre: genreString,
            };

            // Upload files if selected
            if (files.poster) {
                success('Uploading poster...', '');
                submissionData.poster_url = await uploadFile(files.poster, 'image', 'posters');
            } else if (submissionData.poster_url === '') {
                // User explicitly removed the poster
                submissionData.poster_url = null;
            }

            if (files.backdrop) {
                success('Uploading backdrop...', '');
                submissionData.backdrop_url = await uploadFile(files.backdrop, 'image', 'backdrops');
            } else if (submissionData.backdrop_url === '') {
                // User explicitly removed the backdrop
                submissionData.backdrop_url = null;
            }

            if (files.thumbnail) {
                success('Uploading thumbnail...', '');
                submissionData.thumbnail_url = await uploadFile(files.thumbnail, 'image', 'thumbnails');
            } else if (submissionData.thumbnail_url === '') {
                // User explicitly removed the thumbnail
                submissionData.thumbnail_url = null;
            }

            if (files.trailer) {
                success('Uploading trailer...', '');
                submissionData.trailer_url = await uploadFile(files.trailer, 'video', 'trailers');
            } else if (submissionData.trailer_url === '') {
                // User explicitly removed the trailer
                submissionData.trailer_url = null;
            }

            if (content) {
                router.put(`/admin/content/${content.id}`, submissionData, {
                    onSuccess: () => success('Content updated!', 'The content has been updated successfully'),
                    onError: (errors) => {
                        const errorMessage = Object.values(errors).flat().join(", ")
                        showError('Failed to update', errorMessage)
                    },
                });
            } else {
                router.post('/admin/content', submissionData, {
                    onSuccess: () => success('Content created!', 'The content has been created successfully'),
                    onError: (errors) => {
                        const errorMessage = Object.values(errors).flat().join(", ")
                        showError('Failed to create', errorMessage)
                    },
                });
            }
        } catch (error) {
            showError('Upload failed', error instanceof Error ? error.message : 'Failed to upload files');
        } finally {
            setIsSubmitting(false);
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
            title={content ? 'Edit Content' : 'Create Content'}
            header={
                <div>
                    <div className="flex items-center space-x-4 mb-2">
                        <button
                            onClick={() => router.visit('/admin/content')}
                            className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="w-6 h-6" />
                        </button>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            {content ? 'Edit Content' : 'Create New Content'}
                        </h1>
                    </div>
                </div>
            }
        >
            <form onSubmit={handleSubmit} className="space-y-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Basic Information
                    </h3>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title *
                            </label>
                            <input
                                type="text"
                                name="title"
                                value={formData.title}
                                onChange={handleChange}
                                required
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description *
                            </label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                required
                                rows={4}
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Type *
                            </label>
                            <Select
                                value={formData.type}
                                onValueChange={(value) => setFormData({ ...formData, type: value as typeof formData.type })}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select content type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="movie">Movie</SelectItem>
                                    <SelectItem value="show">Show</SelectItem>
                                    <SelectItem value="skit">Skit</SelectItem>
                                    <SelectItem value="afrimation">Afrimation</SelectItem>
                                    <SelectItem value="real_estate">Real Estate</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Genres
                            </label>
                            <MultiSelect
                                options={categories.map((cat) => ({
                                    value: cat.id.toString(),
                                    label: cat.title,
                                }))}
                                value={formData.genre}
                                onChange={(value) => setFormData({ ...formData, genre: value })}
                                placeholder="Select genres..."
                                className="w-full"
                            />
                            <p className="mt-1 text-xs text-gray-500">Content can belong to multiple genres</p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Content Provider *
                            </label>
                            <Select
                                value={formData.provider_id.toString()}
                                onValueChange={(value) => setFormData({ ...formData, provider_id: value })}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select Provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    {providers.map((provider) => (
                                        <SelectItem key={provider.id} value={provider.id.toString()}>
                                            {provider.display_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Visibility *
                            </label>
                            <Select
                                value={formData.visibility}
                                onValueChange={(value) => setFormData({ ...formData, visibility: value as typeof formData.visibility })}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select visibility" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="public">Public</SelectItem>
                                    <SelectItem value="private">Private</SelectItem>
                                    <SelectItem value="premium">Premium</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maturity Rating *
                            </label>
                            <Select
                                value={formData.maturity_rating}
                                onValueChange={(value) => setFormData({ ...formData, maturity_rating: value as typeof formData.maturity_rating })}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select maturity rating" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Ages</SelectItem>
                                    <SelectItem value="pg">PG</SelectItem>
                                    <SelectItem value="pg13">PG-13</SelectItem>
                                    <SelectItem value="r">R</SelectItem>
                                    <SelectItem value="adult">Adult</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Published Date
                                <span className="text-xs text-gray-500 ml-2">(Auto-set for public content)</span>
                            </label>
                            <DatePicker
                                value={formData.published_at}
                                onChange={(value) => setFormData({ ...formData, published_at: value })}
                                placeholder="Select publish date"
                                className="w-full"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Release Year
                            </label>
                            <input
                                type="number"
                                name="release_year"
                                value={formData.release_year}
                                onChange={handleChange}
                                min="1900"
                                max={new Date().getFullYear() + 5}
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Duration (seconds)
                            </label>
                            <input
                                type="number"
                                name="duration_seconds"
                                value={formData.duration_seconds}
                                onChange={handleChange}
                                min="0"
                                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Media Assets
                    </h3>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <ImageUpload
                                label="Poster Image"
                                description="Vertical poster (2:3 aspect ratio recommended)"
                                value={files.poster || formData.poster_url}
                                onChange={(file) => {
                                    setFiles({ ...files, poster: file });
                                    if (!file) {
                                        setFormData({ ...formData, poster_url: '' });
                                    }
                                }}
                                maxSize={5}
                            />
                            {formData.poster_url && !files.poster && (
                                <p className="mt-2 text-xs text-gray-500">Current: {formData.poster_url}</p>
                            )}
                        </div>

                        <div>
                            <ImageUpload
                                label="Thumbnail Image"
                                description="Thumbnail for lists (16:9 aspect ratio recommended)"
                                value={files.thumbnail || formData.thumbnail_url}
                                onChange={(file) => {
                                    setFiles({ ...files, thumbnail: file });
                                    if (!file) {
                                        setFormData({ ...formData, thumbnail_url: '' });
                                    }
                                }}
                                maxSize={5}
                            />
                            {formData.thumbnail_url && !files.thumbnail && (
                                <p className="mt-2 text-xs text-gray-500">Current: {formData.thumbnail_url}</p>
                            )}
                        </div>

                        <div className="md:col-span-2">
                            <ImageUpload
                                label="Backdrop Image"
                                description="Wide background image (16:9 aspect ratio recommended)"
                                value={files.backdrop || formData.backdrop_url}
                                onChange={(file) => {
                                    setFiles({ ...files, backdrop: file });
                                    if (!file) {
                                        setFormData({ ...formData, backdrop_url: '' });
                                    }
                                }}
                                maxSize={10}
                            />
                            {formData.backdrop_url && !files.backdrop && (
                                <p className="mt-2 text-xs text-gray-500">Current: {formData.backdrop_url}</p>
                            )}
                        </div>

                        <div className="md:col-span-2">
                            <VideoUpload
                                label="Trailer Video (Optional)"
                                description="MP4, WebM, MOV up to 2GB"
                                value={files.trailer || formData.trailer_url}
                                onChange={(file) => {
                                    setFiles({ ...files, trailer: file });
                                    if (!file) {
                                        setFormData({ ...formData, trailer_url: '' });
                                    }
                                }}
                                maxSize={2048}
                            />
                            {formData.trailer_url && !files.trailer && (
                                <p className="mt-2 text-xs text-gray-500">Current: {formData.trailer_url}</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={() => router.visit('/admin/content')}
                        disabled={isSubmitting}
                        className="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        className="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50"
                    >
                        <Save className="w-5 h-5 mr-2" />
                        {isSubmitting ? 'Uploading...' : content ? 'Update Content' : 'Create Content'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
