import React from "react";

export default function SidebarChats({
    chats,
    currentChat,
    loadChat,
    createChat,
}) {
    return (
        <aside className="w-64 bg-[#21397D] text-white p-4 flex flex-col">
            <button
                onClick={createChat}
                className="mb-4 bg-white text-[#21397D] rounded-lg px-3 py-2 font-bold"
            >
                + Новый чат
            </button>

            <div className="space-y-2 overflow-y-auto">
                {chats.map((chat) => (
                    <div
                        key={chat.id}
                        onClick={() => loadChat(chat.id)}
                        className={
                            "p-2 rounded-lg cursor-pointer transition " +
                            (currentChat?.id === chat.id
                                ? "bg-[#14224C]"
                                : "hover:bg-[#1A2E67]")
                        }
                    >
                        {chat.title ?? `Чат #${chat.id}`}
                    </div>
                ))}
            </div>
        </aside>
    );
}
