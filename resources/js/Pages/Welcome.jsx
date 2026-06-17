import React from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link } from "@inertiajs/react";

export default function Welcome({ auth = { user: null } }) {
    return (
        <GuestLayout>
            <Head title="Добро пожаловать" />

            <div className="space-y-6 text-gray-900">
                <div>
                    <p className="text-sm font-semibold uppercase text-[#21397D]">
                        TeachAI
                    </p>
                    <h1 className="mt-2 text-2xl font-semibold">
                        Добро пожаловать
                    </h1>
                </div>

                <p className="leading-relaxed text-gray-700">
                    Войдите в систему или зарегистрируйтесь, чтобы открыть
                    личный кабинет TeachAI.
                </p>

                {!auth.user ? (
                    <div className="flex flex-wrap gap-3">
                        <Link
                            href={route("login")}
                            className="rounded bg-[#21397D] px-4 py-2 text-white transition hover:bg-[#2A4A9A]"
                        >
                            Войти
                        </Link>

                        <Link
                            href={route("register")}
                            className="rounded border border-[#21397D] px-4 py-2 text-[#21397D] transition hover:bg-[#21397D] hover:text-white"
                        >
                            Регистрация
                        </Link>
                    </div>
                ) : (
                    <Link
                        href={route("dashboard")}
                        className="inline-flex rounded bg-[#21397D] px-4 py-2 text-white transition hover:bg-[#2A4A9A]"
                    >
                        Перейти в панель
                    </Link>
                )}
            </div>
        </GuestLayout>
    );
}
