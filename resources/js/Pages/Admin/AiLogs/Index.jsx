import AdminLayout from "@/Layouts/AdminLayout";
import { Link, usePage } from "@inertiajs/react";

export default function AiLogs() {
    const { logs } = usePage().props;

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <h1 className="text-3xl font-bold text-[#21397D]">AI Логи</h1>

                <Link
                    href="/admin"
                    className="px-4 py-2 bg-[#21397D] text-white rounded shadow hover:bg-[#1e2d63] transition"
                >
                    ← Назад
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden border">
                <table className="min-w-full text-left">
                    <thead className="bg-[#21397D] text-white">
                        <tr>
                            <th className="py-3 px-4">ID</th>
                            <th className="py-3 px-4">Вопрос</th>
                            <th className="py-3 px-4">Ответ</th>
                            <th className="py-3 px-4">Дата</th>
                            <th className="py-3 px-4 text-right">Действия</th>
                        </tr>
                    </thead>

                    <tbody>
                        {logs.data.map((log) => (
                            <tr
                                key={log.id}
                                className="border-b hover:bg-gray-50"
                            >
                                <td className="py-3 px-4">{log.id}</td>

                                <td className="py-3 px-4 max-w-[280px] truncate">
                                    {log.question}
                                </td>

                                <td className="py-3 px-4 max-w-[280px] truncate">
                                    {log.final_answer}
                                </td>

                                <td className="py-3 px-4">
                                    {new Date(log.created_at).toLocaleString()}
                                </td>

                                <td className="py-3 px-4 text-right">
                                    <Link
                                        href={`/admin/ai-logs/${log.id}`}
                                        className="text-blue-600 hover:underline"
                                    >
                                        Открыть →
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
